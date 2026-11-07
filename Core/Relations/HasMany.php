<?php

namespace Core\Relations;

use Core\Model;
use Core\QueryBuilder;

/**
 * ============================================================================
 * Srin Engine - HasMany Relationship
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class HasMany extends Relation
{
    /**
     * Create a new HasMany relationship instance.
     */
    public function __construct(Model $parent, string $related, string $foreignKey, string $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;

        $parentKey = $parent->getAttribute($localKey);
        $this->query = $related::query()->where($foreignKey, $parentKey);
    }

    /**
     * Get the results of the relationship.
     *
     * @return array<Model>
     */
    public function getResults(): array
    {
        if ($this->parent->getAttribute($this->localKey) === null) {
            return [];
        }

        return $this->query->get();
    }

    /**
     * Create a new related model through the relationship.
     */
    public function create(array $attributes): Model
    {
        $attributes[$this->foreignKey] = $this->parent->getAttribute($this->localKey);
        return $this->related::create($attributes);
    }

    /**
     * Save a related model instance through the relationship.
     */
    public function save(Model $model): Model
    {
        $model->setAttribute($this->foreignKey, $this->parent->getAttribute($this->localKey));
        $model->save();
        return $model;
    }

    /**
     * Prepare query for eager loading.
     *
     * @param array<Model> $models
     */
    public function getEagerQuery(array $models): QueryBuilder
    {
        $keys = [];
        foreach ($models as $model) {
            $val = $model->getAttribute($this->localKey);
            if ($val !== null) {
                $keys[] = $val;
            }
        }
        $keys = array_values(array_unique($keys));

        return $this->related::query()->whereIn($this->foreignKey, $keys);
    }

    /**
     * Match eager loaded results to their parent models.
     *
     * @param array<Model> $models
     * @param array<Model> $results
     * @param string $relation
     * @return array<Model>
     */
    public function match(array $models, array $results, string $relation): array
    {
        $dictionary = [];
        foreach ($results as $result) {
            $key = (string) $result->getAttribute($this->foreignKey);
            if (!isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }
            $dictionary[$key][] = $result;
        }

        foreach ($models as $model) {
            $key = (string) $model->getAttribute($this->localKey);
            $model->setRelation($relation, $dictionary[$key] ?? []);
        }

        return $models;
    }
}
