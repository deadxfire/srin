<?php

namespace Core\Relations;

use Core\Model;
use Core\QueryBuilder;

/**
 * ============================================================================
 * Srin Engine - Base Model Relation
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
abstract class Relation
{
    /**
     * The parent model instance.
     */
    protected Model $parent;

    /**
     * The target related model class name.
     */
    protected string $related;

    /**
     * The foreign key column.
     */
    protected string $foreignKey;

    /**
     * The local or owner key column.
     */
    protected string $localKey;

    /**
     * The underlying query builder instance.
     */
    protected QueryBuilder $query;

    /**
     * Get the parent model.
     */
    public function getParent(): Model
    {
        return $this->parent;
    }

    /**
     * Get the related model class.
     */
    public function getRelated(): string
    {
        return $this->related;
    }

    /**
     * Get the foreign key.
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the local key.
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    /**
     * Get the underlying QueryBuilder.
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    /**
     * Fetch the relationship results.
     */
    abstract public function getResults(): mixed;

    /**
     * Prepare query for eager loading.
     *
     * @param array<Model> $models
     */
    abstract public function getEagerQuery(array $models): QueryBuilder;

    /**
     * Match eager loaded results to their parents.
     *
     * @param array<Model> $models
     * @param array<Model> $results
     * @param string $relation
     * @return array<Model>
     */
    abstract public function match(array $models, array $results, string $relation): array;

    /**
     * Dynamically pass query calls to the QueryBuilder instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        $result = $this->query->$method(...$parameters);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }
}
