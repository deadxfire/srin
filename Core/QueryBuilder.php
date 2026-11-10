<?php

namespace Core;

use Exception;

/**
 * ============================================================================
 * Srin Engine - Fluent SQL Injection Proof Query Builder
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class QueryBuilder
{
    protected string $table;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orders = [];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected ?string $modelClass = null;
    protected array $eagerLoad = [];

    /**
     * Whitelist of safe comparison operators for WHERE conditions.
     */
    protected const ALLOWED_OPERATORS = [
        '=', '!=', '<>', '<', '>', '<=', '>=',
        'LIKE', 'NOT LIKE', 'ILIKE', 'REGEXP'
    ];

    public function __construct(string $table, ?string $modelClass = null)
    {
        $this->table = $table;
        $this->modelClass = $modelClass;
    }

    /**
     * Wrap and sanitize column/table identifiers to prevent SQL identifier injection.
     */
    protected function wrap(string $value): string
    {
        $value = trim($value);

        if ($value === '*') {
            return '*';
        }

        // Handle aliased expressions: "col AS alias"
        if (preg_match('/^(.*?)\s+as\s+(.*)$/i', $value, $matches)) {
            return $this->wrap($matches[1]) . ' AS ' . $this->wrapValue($matches[2]);
        }

        // Handle DISTINCT prefix: e.g. DISTINCT col
        if (preg_match('/^DISTINCT\s+(.+)$/i', $value, $matches)) {
            return 'DISTINCT ' . $this->wrap($matches[1]);
        }

        // Handle safe aggregate functions: e.g. COUNT(*), SUM(col), COUNT(DISTINCT col), COALESCE(col1, col2)
        if (preg_match('/^([a-zA-Z_]+)\((.*)\)$/', $value, $matches)) {
            $func = strtoupper($matches[1]);
            $allowedFuncs = ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX', 'DISTINCT', 'COALESCE'];
            if (in_array($func, $allowedFuncs, true)) {
                $argsStr = trim($matches[2]);
                if ($argsStr === '*') {
                    return "{$func}(*)";
                }
                $rawArgs = array_map('trim', explode(',', $argsStr));
                $wrappedArgs = array_map(function($arg) {
                    if ($arg === '*') {
                        return '*';
                    }
                    if (preg_match('/^DISTINCT\s+(.+)$/i', $arg, $dm)) {
                        return 'DISTINCT ' . $this->wrap($dm[1]);
                    }
                    return $this->wrap($arg);
                }, $rawArgs);
                return "{$func}(" . implode(', ', $wrappedArgs) . ")";
            }
        }

        return $this->wrapValue($value);
    }

    /**
     * Wrap a single column/table value.
     */
    protected function wrapValue(string $value): string
    {
        $value = trim($value);
        if ($value === '*') {
            return '*';
        }

        // Validate identifier before any lossy sanitization
        if (!preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*$/', $value)) {
            throw new \InvalidArgumentException("Invalid SQL identifier: [{$value}]");
        }

        $parts = explode('.', $value);
        $wrapped = array_map(fn($part) => '`' . trim($part) . '`', $parts);
        return implode('.', $wrapped);
    }

    /**
     * Sanitize operator against allowed whitelist.
     */
    protected function cleanOperator(mixed $operator): string
    {
        $op = strtoupper(trim((string) $operator));
        if (!in_array($op, self::ALLOWED_OPERATORS, true)) {
            throw new \InvalidArgumentException("Invalid SQL operator: [{$op}]");
        }
        return $op;
    }

    /**
     * Set columns to select.
     */
    public function select(array|string $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a WHERE condition.
     */
    public function where(string $column, mixed $operator = null, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if ($value === null) {
            $type = ($operator === '!=' || $operator === '<>') ? 'not_null' : 'null';
            $this->wheres[] = [
                'type'    => $type,
                'boolean' => 'AND',
                'column'  => $column,
            ];
            return $this;
        }

        $this->wheres[] = [
            'type'     => 'basic',
            'boolean'  => 'AND',
            'column'   => $column,
            'operator' => $this->cleanOperator($operator),
            'value'    => $value,
        ];

        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add an OR WHERE condition.
     */
    public function orWhere(string $column, mixed $operator = null, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if ($value === null) {
            $type = ($operator === '!=' || $operator === '<>') ? 'not_null' : 'null';
            $this->wheres[] = [
                'type'    => $type,
                'boolean' => 'OR',
                'column'  => $column,
            ];
            return $this;
        }

        $this->wheres[] = [
            'type'     => 'basic',
            'boolean'  => 'OR',
            'column'   => $column,
            'operator' => $this->cleanOperator($operator),
            'value'    => $value,
        ];

        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add a WHERE IN condition.
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        if (empty($values)) {
            $this->wheres[] = [
                'type'    => 'raw',
                'boolean' => $boolean,
                'sql'     => '0 = 1',
            ];
            return $this;
        }

        $this->wheres[] = [
            'type'    => 'in',
            'boolean' => $boolean,
            'column'  => $column,
            'values'  => array_values($values),
        ];

        foreach ($values as $v) {
            $this->bindings[] = $v;
        }

        return $this;
    }

    /**
     * Add a WHERE NULL condition.
     */
    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type'    => 'null',
            'boolean' => $boolean,
            'column'  => $column,
        ];
        return $this;
    }

    /**
     * Add a WHERE NOT NULL condition.
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type'    => 'not_null',
            'boolean' => $boolean,
            'column'  => $column,
        ];
        return $this;
    }

    /**
     * Add a WHERE LIKE condition.
     * Automatically wraps value with % if wildcards are omitted.
     */
    public function whereLike(string $column, string $pattern, string $boolean = 'AND'): self
    {
        if (!str_contains($pattern, '%')) {
            $pattern = "%{$pattern}%";
        }

        $this->wheres[] = [
            'type'     => 'basic',
            'boolean'  => $boolean,
            'column'   => $column,
            'operator' => 'LIKE',
            'value'    => $pattern,
        ];

        $this->bindings[] = $pattern;
        return $this;
    }

    /**
     * Apply a callback when a condition is met / truthy.
     * Example: $query->when($category, fn($q, $val) => $q->where('category', $val));
     */
    public function when(mixed $value, callable $callback, ?callable $default = null): self
    {
        $condition = ($value instanceof \Closure) ? $value($this) : !empty($value);

        if ($condition) {
            $callback($this, $value);
        } elseif ($default !== null) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Search across one or multiple columns with SQL LIKE.
     * Automatically skips cleanly if keyword is empty/null.
     * Example: $query->search('title', $q)
     * Example: $query->search(['title', 'content', 'author'], $q)
     */
    public function search(string|array $columns, ?string $keyword): self
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return $this;
        }

        $cols = is_array($columns) ? $columns : [$columns];
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $keyword);
        $pattern = "%{$escaped}%";

        $clauses = [];
        foreach ($cols as $col) {
            $clauses[] = $this->wrap($col) . " LIKE ? ESCAPE '!'";
            $this->bindings[] = $pattern;
        }

        $this->wheres[] = [
            'type'    => 'raw',
            'boolean' => 'AND',
            'sql'     => '(' . implode(' OR ', $clauses) . ')',
        ];

        return $this;
    }

    /**
     * Apply an associative array of key-value filters.
     * Automatically ignores empty strings or null values.
     * Example: $query->filter(['category' => $cat, 'author_id' => $authorId])
     */
    public function filter(array $filters): self
    {
        foreach ($filters as $column => $value) {
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }

            if (is_array($value)) {
                $this->whereIn($column, $value);
            } else {
                $this->where($column, $value);
            }
        }

        return $this;
    }

    /**
     * Order by column.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = $this->wrap($column) . " {$dir}";
        return $this;
    }

    /**
     * Order by latest timestamp/id.
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * Order by oldest timestamp/id.
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'ASC');
    }

    /**
     * Limit number of results.
     */
    public function limit(int $limit): self
    {
        $this->limitValue = max(0, $limit);
        return $this;
    }

    /**
     * Offset results.
     */
    public function offset(int $offset): self
    {
        $this->offsetValue = max(0, $offset);
        return $this;
    }

    /**
     * Build the SELECT SQL string.
     */
    public function toSql(): string
    {
        $wrappedCols = array_map([$this, 'wrap'], $this->columns);
        $cols = implode(', ', $wrappedCols);
        $table = $this->wrap($this->table);
        $sql = "SELECT {$cols} FROM {$table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        // SQLite & MySQL identifier normalization
        if (Database::getDriver() === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }

        return $sql;
    }

    /**
     * Compile WHERE clauses.
     */
    protected function buildWheres(): string
    {
        $clauses = [];

        foreach ($this->wheres as $i => $w) {
            $prefix = ($i === 0) ? '' : $w['boolean'] . ' ';
            $column = isset($w['column']) ? $this->wrap($w['column']) : '';

            switch ($w['type']) {
                case 'basic':
                    $clauses[] = "{$prefix}{$column} {$w['operator']} ?";
                    break;

                case 'in':
                    $placeholders = implode(', ', array_fill(0, count($w['values']), '?'));
                    $clauses[] = "{$prefix}{$column} IN ({$placeholders})";
                    break;

                case 'null':
                    $clauses[] = "{$prefix}{$column} IS NULL";
                    break;

                case 'not_null':
                    $clauses[] = "{$prefix}{$column} IS NOT NULL";
                    break;

                case 'raw':
                    $clauses[] = "{$prefix}{$w['sql']}";
                    break;
            }
        }

        return implode(' ', $clauses);
    }

    /**
     * Set relations to be eager loaded with models.
     */
    public function with(string|array $relations): self
    {
        $this->eagerLoad = array_merge($this->eagerLoad, is_array($relations) ? $relations : func_get_args());
        return $this;
    }

    /**
     * Execute SELECT query and return array of records or Model instances.
     */
    public function get(): array
    {
        $rows = Database::select($this->toSql(), $this->bindings);

        if ($this->modelClass && class_exists($this->modelClass)) {
            $models = [];
            foreach ($rows as $row) {
                $models[] = new $this->modelClass($row, true);
            }

            if (!empty($this->eagerLoad) && !empty($models)) {
                $models = $this->eagerLoadRelations($models);
            }

            return $models;
        }

        return $rows;
    }

    /**
     * Eager load relations for the model set.
     */
    protected function eagerLoadRelations(array $models): array
    {
        foreach ($this->eagerLoad as $relationName) {
            $dummy = new $this->modelClass();
            if (method_exists($dummy, $relationName)) {
                $relation = $dummy->$relationName();
                if ($relation instanceof \Core\Relations\Relation) {
                    $eagerQuery = $relation->getEagerQuery($models);
                    $results = $eagerQuery->get();
                    $models = $relation->match($models, $results, $relationName);
                }
            }
        }
        return $models;
    }

    /**
     * Get the first result or null.
     */
    public function first(): mixed
    {
        $clone = clone $this;
        $clone->limit(1);
        $results = $clone->get();
        return $results[0] ?? null;
    }

    /**
     * Find a record by its primary key.
     */
    public function find(mixed $id, string $primaryKey = 'id'): mixed
    {
        return (clone $this)->where($primaryKey, $id)->first();
    }

    /**
     * Return row count matching the query.
     */
    public function count(): int
    {
        $clone = clone $this;
        $clone->columns = ['COUNT(*) as aggregate'];
        $clone->orders = [];
        $sql = $clone->toSql();
        $row = Database::select($sql, $clone->bindings);
        return (int) ($row[0]['aggregate'] ?? 0);
    }

    /**
     * Calculate sum of column values.
     */
    public function sum(string $column): int|float
    {
        $clone = clone $this;
        $cleanCol = $this->wrap($column);
        $clone->columns = ["SUM({$cleanCol}) as aggregate"];
        $clone->orders = [];
        $sql = $clone->toSql();
        $row = Database::select($sql, $clone->bindings);
        return $row[0]['aggregate'] !== null ? (float) $row[0]['aggregate'] : 0;
    }

    /**
     * Calculate average of column values.
     */
    public function avg(string $column): int|float
    {
        $clone = clone $this;
        $cleanCol = $this->wrap($column);
        $clone->columns = ["AVG({$cleanCol}) as aggregate"];
        $clone->orders = [];
        $sql = $clone->toSql();
        $row = Database::select($sql, $clone->bindings);
        return $row[0]['aggregate'] !== null ? (float) $row[0]['aggregate'] : 0;
    }

    /**
     * Retrieve minimum value of column.
     */
    public function min(string $column): mixed
    {
        $clone = clone $this;
        $cleanCol = $this->wrap($column);
        $clone->columns = ["MIN({$cleanCol}) as aggregate"];
        $clone->orders = [];
        $sql = $clone->toSql();
        $row = Database::select($sql, $clone->bindings);
        return $row[0]['aggregate'] ?? null;
    }

    /**
     * Retrieve maximum value of column.
     */
    public function max(string $column): mixed
    {
        $clone = clone $this;
        $cleanCol = $this->wrap($column);
        $clone->columns = ["MAX({$cleanCol}) as aggregate"];
        $clone->orders = [];
        $sql = $clone->toSql();
        $row = Database::select($sql, $clone->bindings);
        return $row[0]['aggregate'] ?? null;
    }

    /**
     * Check if any record exists.
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Return clean array of distinct non-empty values for a column.
     * Zero-memory database-level extraction.
     * Example: Post::where('is_published', true)->distinctValues('category');
     */
    public function distinctValues(string $column): array
    {
        $cleanCol = $this->wrap($column);
        $q = clone $this;
        $q->columns = ["DISTINCT {$cleanCol} AS _val"];
        $q->orders = ["_val ASC"];
        $q->limitValue = null;
        $q->offsetValue = null;

        $rows = Database::select($q->toSql(), $q->bindings);
        $values = [];
        foreach ($rows as $row) {
            $v = $row['_val'] ?? null;
            if ($v !== null && $v !== '') {
                $values[] = $v;
            }
        }
        return array_values(array_unique($values));
    }

    /**
     * Get an array of values from a single column, optionally keyed by another column.
     * Example: Post::pluck('title') => ['First Post', 'Second Post']
     * Example: Post::pluck('title', 'id') => [1 => 'First Post', 2 => 'Second Post']
     */
    public function pluck(string $column, ?string $key = null, bool $distinct = false): array
    {
        $cols = [$this->wrap($column) . ' AS _val'];
        if ($key !== null) {
            $cols[] = $this->wrap($key) . ' AS _key';
        }
        if ($distinct) {
            $cols[0] = 'DISTINCT ' . $cols[0];
        }

        $q = clone $this;
        $q->columns = $cols;
        $rows = Database::select($q->toSql(), $q->bindings);

        $result = [];
        foreach ($rows as $row) {
            if ($key !== null && array_key_exists('_key', $row)) {
                $result[$row['_key']] = $row['_val'] ?? null;
            } else {
                $result[] = $row['_val'] ?? null;
            }
        }
        return $result;
    }

    /**
     * Increment a column's value by a given amount.
     */
    public function increment(string $column, int $amount = 1): int
    {
        $cleanCol = str_replace(['`', '"', "'", ';', '--'], '', (string) $column);
        $whereSql = !empty($this->wheres) ? ' WHERE ' . $this->buildWheres() : '';
        $table = $this->wrap($this->table);
        $sql = "UPDATE {$table} SET `{$cleanCol}` = `{$cleanCol}` + ?{$whereSql}";
        if (Database::getDriver() === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }
        return Database::update($sql, array_merge([$amount], $this->bindings));
    }

    /**
     * Decrement a column's value by a given amount.
     */
    public function decrement(string $column, int $amount = 1): int
    {
        return $this->increment($column, -$amount);
    }

    /**
     * Chunk results to process large datasets efficiently.
     */
    public function chunk(int $size, callable $callback): bool
    {
        $page = 1;
        do {
            $q = clone $this;
            $items = $q->limit($size)->offset(($page - 1) * $size)->get();
            $count = count($items);
            if ($count === 0) {
                break;
            }
            if ($callback($items, $page) === false) {
                return false;
            }
            unset($items);
            $page++;
        } while ($count === $size);

        return true;
    }

    /**
     * Paginate the query results into a Paginator instance.
     */
    public function paginate(int $perPage = 15, ?int $page = null, string $pageName = 'page'): Paginator
    {
        $perPage = max(1, $perPage);

        if ($page === null) {
            $rawPage = $_GET[$pageName] ?? 1;
            $page = is_numeric($rawPage) ? (int) $rawPage : 1;
        }

        $maxSafePage = (int) floor(PHP_INT_MAX / $perPage);
        $page = max(1, min($page, $maxSafePage));
        $offset = (int) (($page - 1) * $perPage);

        // Clone query for total count without orders, limits, or offsets
        $countQuery = clone $this;
        $countQuery->columns = ['COUNT(*) as aggregate'];
        $countQuery->orders = [];
        $countQuery->limitValue = null;
        $countQuery->offsetValue = null;
        $sql = $countQuery->toSql();
        $row = Database::select($sql, $countQuery->bindings);
        $total = (int) ($row[0]['aggregate'] ?? 0);

        // Fetch paginated slice of records on a cloned query so builder is not mutated
        $sliceQuery = clone $this;
        $sliceQuery->limit($perPage)->offset($offset);
        $items = $sliceQuery->get();

        return new Paginator($items, $total, $perPage, $page, $pageName);
    }

    /**
     * Insert a record into the table.
     */
    public function insert(array $values): int|string
    {
        if (empty($values)) {
            throw new Exception("Cannot insert empty values into table [{$this->table}].");
        }

        $columns = array_keys($values);
        $cleanCols = array_map(fn($c) => $this->wrapValue((string) $c), $columns);
        $placeholders = array_fill(0, count($columns), '?');

        $colsSql = implode(', ', $cleanCols);
        $placeholdersSql = implode(', ', $placeholders);
        $table = $this->wrap($this->table);

        $sql = "INSERT INTO {$table} ({$colsSql}) VALUES ({$placeholdersSql})";
        if (Database::getDriver() === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }

        return Database::insert($sql, array_values($values));
    }

    /**
     * Update records matching current WHERE conditions.
     */
    public function update(array $values): int
    {
        if (empty($values)) {
            return 0;
        }

        $sets = [];
        $bindings = [];

        foreach ($values as $col => $val) {
            $cleanCol = $this->wrapValue((string) $col);
            $sets[] = "{$cleanCol} = ?";
            $bindings[] = $val;
        }

        $setSql = implode(', ', $sets);
        $whereSql = !empty($this->wheres) ? ' WHERE ' . $this->buildWheres() : '';
        $table = $this->wrap($this->table);

        // Combine update bindings with where bindings
        $allBindings = array_merge($bindings, $this->bindings);

        $sql = "UPDATE {$table} SET {$setSql}{$whereSql}";
        if (Database::getDriver() === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }

        return Database::update($sql, $allBindings);
    }

    /**
     * Delete records matching current WHERE conditions.
     */
    public function delete(): int
    {
        $whereSql = !empty($this->wheres) ? ' WHERE ' . $this->buildWheres() : '';
        $table = $this->wrap($this->table);
        $sql = "DELETE FROM {$table}{$whereSql}";
        if (Database::getDriver() === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }

        return Database::delete($sql, $this->bindings);
    }
}

