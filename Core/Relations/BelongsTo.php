<?php

namespace Core\Relations;

use Core\Model;
use Core\QueryBuilder;

/**
 * ============================================================================
 * Srin Engine - BelongsTo Relationship
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class BelongsTo extends Relation
{
    /**
     * Create a new BelongsTo relationship instance.
     */
    public function __construct(Model $parent, string $related, string $foreignKey, string $ownerKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $ownerKey; // In BelongsTo, localKey refers to the ownerKey on the related model

        $foreignVal = $parent->getAttribute($foreignKey);
        $this->query = $related::query()->where($ownerKey, $foreignVal);
    }

    /**
     * Get the result of the relationship.
     */
    public function getResults(): ?Model
    {
        if ($this->parent->getAttribute($this->foreignKey) === null) {
            return null;
        }

        return $this->query->first();
    }

    /**
     * Associate the model instance to the given parent model.
     */
    public function associate(Model $model): Model
    {
        $this->parent->setAttribute($this->foreignKey, $model->getAttribute($this->localKey));
        return $this->parent;
    }

    /**
     * Dissociate the parent model from the relationship.
     */
    public function dissociate(): Model
    {
        $this->parent->setAttribute($this->foreignKey, null);
        return $this->parent;
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
            $val = $model->getAttribute($this->foreignKey);
            if ($val !== null) {
                $keys[] = $val;
            }
        }
        $keys = array_values(array_unique($keys));

        return $this->related::query()->whereIn($this->localKey, $keys);
    }

    /**
     * Match eager loaded results to their child models.
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
            $key = (string) $result->getAttribute($this->localKey);
            $dictionary[$key] = $result;
        }

        foreach ($models as $model) {
            $key = (string) $model->getAttribute($this->foreignKey);
            $model->setRelation($relation, $dictionary[$key] ?? null);
        }

        return $models;
    }
}
