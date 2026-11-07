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

    public function __construct(string $table, ?string $modelClass = null)
    {
        $this->table = $table;
        $this->modelClass = $modelClass;
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

        $this->wheres[] = [
            'type'     => 'basic',
            'boolean'  => 'AND',
            'column'   => $column,
            'operator' => strtoupper((string) $operator),
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

        $this->wheres[] = [
            'type'     => 'basic',
            'boolean'  => 'OR',
            'column'   => $column,
            'operator' => strtoupper((string) $operator),
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
     * Order by column.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = "{$column} {$dir}";
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
    protected function toSql(): string
    {
        $cols = implode(', ', $this->columns);
        $sql = "SELECT {$cols} FROM `{$this->table}`";

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

            switch ($w['type']) {
                case 'basic':
                    $clauses[] = "{$prefix}`{$w['column']}` {$w['operator']} ?";
                    break;

                case 'in':
                    $placeholders = implode(', ', array_fill(0, count($w['values']), '?'));
                    $clauses[] = "{$prefix}`{$w['column']}` IN ({$placeholders})";
                    break;

                case 'null':
                    $clauses[] = "{$prefix}`{$w['column']}` IS NULL";
                    break;

                case 'not_null':
                    $clauses[] = "{$prefix}`{$w['column']}` IS NOT NULL";
                    break;

                case 'raw':
                    $clauses[] = "{$prefix}{$w['sql']}";
                    break;
            }
        }

        return implode(' ', $clauses);
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
            return $models;
        }

        return $rows;
    }

    /**
     * Get the first result or null.
     */
    public function first(): mixed
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Find a record by its primary key.
     */
    public function find(mixed $id, string $primaryKey = 'id'): mixed
    {
        return $this->where($primaryKey, $id)->first();
    }

    /**
     * Return row count matching the query.
     */
    public function count(): int
    {
        $this->columns = ['COUNT(*) as aggregate'];
        $sql = $this->toSql();
        $row = Database::select($sql, $this->bindings);
        return (int) ($row[0]['aggregate'] ?? 0);
    }

    /**
     * Check if any record exists.
     */
    public function exists(): bool
    {
        return $this->count() > 0;
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
        $placeholders = array_fill(0, count($columns), '?');

        $colsSql = '`' . implode('`, `', $columns) . '`';
        $placeholdersSql = implode(', ', $placeholders);

        $sql = "INSERT INTO `{$this->table}` ({$colsSql}) VALUES ({$placeholdersSql})";
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
            $sets[] = "`{$col}` = ?";
            $bindings[] = $val;
        }

        $setSql = implode(', ', $sets);
        $whereSql = !empty($this->wheres) ? ' WHERE ' . $this->buildWheres() : '';

        // Combine update bindings with where bindings
        $allBindings = array_merge($bindings, $this->bindings);

        $sql = "UPDATE `{$this->table}` SET {$setSql}{$whereSql}";
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
        $sql = "DELETE FROM `{$this->table}`{$whereSql}";
        if (Database::getDriver() === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }

        return Database::delete($sql, $this->bindings);
    }
}
