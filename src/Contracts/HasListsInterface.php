<?php

declare(strict_types=1);

namespace Blamodex\Lists\Contracts;

use Blamodex\Lists\Models\Lists;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface for models that can own lists.
 *
 * Implement this interface on models that should be able to create and manage lists,
 * such as User, Team, or Organization models.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
interface HasListsInterface
{
    /**
     * Get all lists owned by this model.
     *
     * @return MorphMany<Lists, covariant TModel>
     */
    public function lists(): MorphMany;

    /**
     * Create a new list owned by this model.
     *
     * @param array<string, mixed> $attributes
     */
    public function createList(array $attributes): Lists;

    /**
     * Update an existing list owned by this model.
     *
     * @param array<string, mixed> $attributes
     */
    public function updateList(Lists $list, array $attributes): Lists;

    /**
     * Delete a list owned by this model.
     */
    public function deleteList(Lists $list): bool;
}
