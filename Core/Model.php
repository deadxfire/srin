<?php

namespace Core;

use ArrayAccess;
use JsonSerializable;
use Exception;
use Core\Relations\Relation;
use Core\Relations\HasMany;
use Core\Relations\HasOne;
use Core\Relations\BelongsTo;

/**
 * ============================================================================
 * Srin Engine - ActiveRecord Base Model
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
abstract class Model implements ArrayAccess, JsonSerializable
{
    /**
     * Database table associated with the model.
     */
    protected static string $table = '';

    /**
     * Primary key column.
     */
    protected static string $primaryKey = 'id';

    /**
     * Indicates if the model should manage created_at & updated_at timestamps.
     */
    public bool $timestamps = true;

    /**
     * Mass-assignable attributes.
     */
    protected array $fillable = [];

    /**
     * Guarded attributes.
     */
    protected array $guarded = ['id'];

    /**
     * The model's attributes.
     */
    protected array $attributes = [];

    /**
     * The model's original attributes.
     */
    protected array $original = [];

    /**
     * Loaded relationship instances.
     * @var array<string, mixed>
     */
    protected array $relations = [];

    /**
     * Indicates if the model exists in the database.
     */
    public bool $exists = false;

    /**
     * Create a new model instance.
     */
    public function __construct(array $attributes = [], bool $exists = false)
    {
        if ($exists) {
            $this->attributes = $attributes;
        } else {
            $this->fill($attributes);
        }

        $this->exists = $exists;
        $this->original = $this->attributes;
    }

    /**
     * Get table name for the model.
     */
    public static function getTable(): string
    {
        if (!empty(static::$table)) {
            return static::$table;
        }

        // Convert ClassName to plural snake_case (e.g. BlogPost -> blog_posts, User -> users)
        $short = (new \ReflectionClass(static::class))->getShortName();
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short));

        // Simple English pluralization
        if (str_ends_with($snake, 'y') && !preg_match('/[aeiou]y$/', $snake)) {
            return substr($snake, 0, -1) . 'ies';
        }
        if (str_ends_with($snake, 's') || str_ends_with($snake, 'x') || str_ends_with($snake, 'ch') || str_ends_with($snake, 'sh')) {
            return $snake . 'es';
        }

        return $snake . 's';
    }

    /**
     * Get primary key column name.
     */
    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    /**
     * Start a new query on the model's table.
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::getTable(), static::class);
    }

    /**
     * Fill the model with an array of attributes respecting fillable & guarded.
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Check if an attribute is mass-assignable.
     */
    public function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded, true)) {
            return false;
        }

        if (empty($this->fillable)) {
            return true;
        }

        return in_array($key, $this->fillable, true);
    }

    /**
     * Fetch all records as Model instances.
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    /**
     * Find a record by its primary key.
     */
    public static function find(mixed $id): ?static
    {
        return static::query()->find($id, static::getPrimaryKey());
    }

    /**
     * Find a record by primary key or abort with HTTP 404.
     */
    public static function findOrFail(mixed $id): static
    {
        $model = static::find($id);
        if (!$model) {
            Response::renderError(404, 'The requested ' . (new \ReflectionClass(static::class))->getShortName() . ' was not found.');
            exit;
        }
        return $model;
    }

    /**
     * Add a WHERE condition to the query.
     */
    public static function where(string $column, mixed $operator = null, mixed $value = null): QueryBuilder
    {
        $q = static::query();
        return $q->where(...func_get_args());
    }

    /**
     * Get the first record matching criteria.
     */
    public static function first(): ?static
    {
        return static::query()->first();
    }

    /**
     * Order by latest timestamp.
     */
    public static function latest(string $column = 'created_at'): QueryBuilder
    {
        return static::query()->latest($column);
    }

    /**
     * Order by oldest timestamp.
     */
    public static function oldest(string $column = 'created_at'): QueryBuilder
    {
        return static::query()->oldest($column);
    }

    /**
     * Count all records in the table.
     */
    public static function count(): int
    {
        return static::query()->count();
    }

    /**
     * Search across one or multiple columns with SQL LIKE.
     * Example: Post::search('title', $q)->get();
     */
    public static function search(string|array $columns, ?string $keyword): QueryBuilder
    {
        return static::query()->search($columns, $keyword);
    }

    /**
     * Apply an associative array of key-value filters.
     * Example: Post::filter(['category' => $cat, 'status' => 'published'])->get();
     */
    public static function filter(array $filters): QueryBuilder
    {
        return static::query()->filter($filters);
    }

    /**
     * Return distinct non-empty values for a column.
     * Example: Post::distinctValues('category');
     */
    public static function distinctValues(string $column): array
    {
        return static::query()->distinctValues($column);
    }

    /**
     * Pluck values from a single column.
     * Example: Post::pluck('title') => ['First Post', 'Second Post']
     */
    public static function pluck(string $column, ?string $key = null, bool $distinct = false): array
    {
        return static::query()->pluck($column, $key, $distinct);
    }

    /**
     * Apply conditional clause when truthy.
     */
    public static function when(mixed $value, callable $callback, ?callable $default = null): QueryBuilder
    {
        return static::query()->when($value, $callback, $default);
    }

    /**
     * Forward any static calls directly to a new QueryBuilder instance or static handler.
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        if ($method === 'remove') {
            return static::destroy($parameters[0] ?? null);
        }

        return static::query()->$method(...$parameters);
    }

    /**
     * Start a new query on the model's table with optional associative criteria.
     * Unique Srin fetcher replacing verbose where chaining.
     *
     * Example:
     *   Post::fetch(['is_published' => true, 'category' => 'tech'])->latest()->get();
     */
    public static function fetch(array $criteria = []): QueryBuilder
    {
        $q = static::query();
        foreach ($criteria as $column => $value) {
            $q->where($column, $value);
        }
        return $q;
    }

    /**
     * Retrieve a single record matching criteria or automatically render 404.
     * Unique Srin method replacing findOrFail / firstOrFail.
     *
     * Example:
     *   $post = Post::one(['slug' => $slug]);
     *   $post = Post::one($id);
     */
    public static function one(array|int|string $criteria): static
    {
        if (is_int($criteria) || (is_string($criteria) && ctype_digit($criteria))) {
            return static::pick((int) $criteria);
        }

        if (is_string($criteria)) {
            $criteria = ['slug' => $criteria];
        }

        $record = static::fetch($criteria)->first();
        if (!$record) {
            Response::renderError(404, 'The requested ' . (new \ReflectionClass(static::class))->getShortName() . ' was not found.');
            exit;
        }

        return $record;
    }

    /**
     * Pick a single model by primary key or automatically render 404.
     * Expressive Srin alternative to findOrFail().
     */
    public static function pick(int|string $id): static
    {
        return static::findOrFail($id);
    }

    /**
     * Insert and persist a new model instance.
     * Expressive Srin alternative to create().
     */
    public static function insert(array $attributes): static
    {
        return static::create($attributes);
    }

    /**
     * Modify an existing record by ID or criteria.
     *
     * Example:
     *   Post::modify($id, ['title' => 'Updated']);
     */
    public static function modify(int|string|array $idOrCriteria, array $attributes): bool
    {
        if (is_array($idOrCriteria)) {
            return static::fetch($idOrCriteria)->update($attributes) >= 0;
        }

        $record = static::find($idOrCriteria);
        if (!$record) {
            return false;
        }

        return $record->update($attributes);
    }

    /**
     * Remove the current model instance from the database.
     * Expressive Srin alternative to delete().
     */
    public function remove(): bool
    {
        return $this->delete();
    }

    /**
     * Increment a numeric counter column and save immediately.
     * Example: $article->hit('views');
     */
    public function hit(string $column = 'views', int $amount = 1): bool
    {
        $current = (int) ($this->attributes[$column] ?? 0);
        $this->attributes[$column] = $current + $amount;
        return $this->save();
    }

    /**
     * Paginate records directly with optional filter criteria.
     * Example: Post::page(10, ['is_published' => true]);
     */
    public static function page(int $perPage = 15, array $criteria = []): Paginator
    {
        return static::fetch($criteria)->paginate($perPage);
    }

    /**
     * Retrieve distinct non-empty values for a column with optional criteria.
     * Example: Post::values('category', ['is_published' => true]);
     */
    public static function values(string $column, array $criteria = []): array
    {
        return static::fetch($criteria)->distinctValues($column);
    }

    /**
     * Paginate models directly.
     */
    public static function paginate(int $perPage = 15, ?int $page = null, string $pageName = 'page'): Paginator
    {
        return static::query()->paginate($perPage, $page, $pageName);
    }

    /**
     * Create and persist a new model instance.
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Save or update the model to database.
     */
    public function save(): bool
    {
        $pk = static::getPrimaryKey();
        $now = date('Y-m-d H:i:s');

        if ($this->exists) {
            // Update existing record
            if ($this->timestamps) {
                $this->attributes['updated_at'] = $now;
            }

            $id = $this->attributes[$pk] ?? null;
            if (!$id) {
                throw new Exception("Cannot update model without primary key [{$pk}].");
            }

            $affected = static::query()->where($pk, $id)->update($this->attributes);
            $this->original = $this->attributes;
            return $affected >= 0;
        }

        // Insert new record
        if ($this->timestamps) {
            if (!isset($this->attributes['created_at'])) {
                $this->attributes['created_at'] = $now;
            }
            if (!isset($this->attributes['updated_at'])) {
                $this->attributes['updated_at'] = $now;
            }
        }

        $id = static::query()->insert($this->attributes);
        if ($id) {
            $this->attributes[$pk] = $id;
        }

        $this->exists = true;
        $this->original = $this->attributes;
        return true;
    }

    /**
     * Update attributes and save.
     */
    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Delete the model from the database.
     */
    public function delete(): bool
    {
        $pk = static::getPrimaryKey();
        $id = $this->attributes[$pk] ?? null;

        if (!$this->exists || !$id) {
            return false;
        }

        $affected = static::query()->where($pk, $id)->delete();
        $this->exists = false;
        return $affected > 0;
    }

    /**
     * Destroy records by primary key.
     */
    public static function destroy(mixed $ids): int
    {
        $ids = is_array($ids) ? $ids : func_get_args();
        $pk = static::getPrimaryKey();
        return static::query()->whereIn($pk, $ids)->delete();
    }

    /**
     * Start a new query on the model's table with optional eager loaded relations.
     */
    public static function with(string|array $relations): QueryBuilder
    {
        return static::query()->with($relations);
    }

    /**
     * Get a single attribute.
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set a single attribute.
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Get a loaded relationship result.
     */
    public function getRelation(string $name): mixed
    {
        return $this->relations[$name] ?? null;
    }

    /**
     * Set a loaded relationship result.
     */
    public function setRelation(string $name, mixed $value): self
    {
        $this->relations[$name] = $value;
        return $this;
    }

    /**
     * Determine if the given relation is loaded.
     */
    public function relationLoaded(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    /**
     * Get the default foreign key name for the model.
     */
    public function getForeignKey(): string
    {
        $short = (new \ReflectionClass(static::class))->getShortName();
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short));
        return $snake . '_id';
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $related The related model class
     * @param string|null $foreignKey Foreign key on the related table
     * @param string|null $localKey Local key on this model's table
     */
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: static::getPrimaryKey();
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param string $related The related model class
     * @param string|null $foreignKey Foreign key on the related table
     * @param string|null $localKey Local key on this model's table
     */
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: static::getPrimaryKey();
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or one-to-many relationship.
     *
     * @param string $related The related model class
     * @param string|null $foreignKey Foreign key on this model's table
     * @param string|null $ownerKey Primary key on the related model's table
     */
    public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        $short = (new \ReflectionClass($related))->getShortName();
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short));
        $foreignKey = $foreignKey ?: $snake . '_id';
        $ownerKey = $ownerKey ?: $related::getPrimaryKey();
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    /**
     * Eager load relations on an existing model instance.
     */
    public function load(string|array $relations): self
    {
        $relations = is_array($relations) ? $relations : func_get_args();
        $dummyList = [$this];

        foreach ($relations as $relationName) {
            if (method_exists($this, $relationName)) {
                $relation = $this->$relationName();
                if ($relation instanceof Relation) {
                    $eagerQuery = $relation->getEagerQuery($dummyList);
                    $results = $eagerQuery->get();
                    $relation->match($dummyList, $results, $relationName);
                }
            }
        }

        return $this;
    }

    /**
     * Convert the model's attributes to an array.
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;

        foreach ($this->relations as $name => $relation) {
            if (is_array($relation)) {
                $attributes[$name] = array_map(fn($item) => $item instanceof Model ? $item->toArray() : $item, $relation);
            } elseif ($relation instanceof Model) {
                $attributes[$name] = $relation->toArray();
            } else {
                $attributes[$name] = $relation;
            }
        }

        return $attributes;
    }

    /**
     * Convert the model to JSON.
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * JsonSerializable implementation.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Magic property getter with lazy-loading relationship support.
     */
    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof Relation) {
                $results = $relation->getResults();
                $this->relations[$key] = $results;
                return $results;
            }
        }

        return null;
    }

    /**
     * Magic property setter.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Magic isset check.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    /**
     * Magic unset.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool { return isset($this->attributes[$offset]); }
    public function offsetGet(mixed $offset): mixed { return $this->attributes[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void { $this->attributes[$offset] = $value; }
    public function offsetUnset(mixed $offset): void { unset($this->attributes[$offset]); }
}
