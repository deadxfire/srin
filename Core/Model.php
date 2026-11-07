<?php

namespace Core;

use ArrayAccess;
use JsonSerializable;
use Exception;

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
     * Convert the model's attributes to an array.
     */
    public function toArray(): array
    {
        return $this->attributes;
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
     * Magic property getter.
     */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
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
        return isset($this->attributes[$key]);
    }

    /**
     * Magic unset.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool { return isset($this->attributes[$offset]); }
    public function offsetGet(mixed $offset): mixed { return $this->attributes[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void { $this->attributes[$offset] = $value; }
    public function offsetUnset(mixed $offset): void { unset($this->attributes[$offset]); }
}
