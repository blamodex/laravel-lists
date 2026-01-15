<?php

declare(strict_types=1);

namespace Blamodex\Lists\Traits;

use Blamodex\Lists\Exceptions\ListOwnershipException;
use Blamodex\Lists\Models\Lists;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for models that can own lists.
 *
 * Use this trait on models that should be able to create and manage lists,
 * such as User, Team, or Organization models.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasLists
{
    /**
     * Get all lists owned by this model.
     *
     * @return MorphMany<Lists, $this>
     */
    public function lists(): MorphMany
    {
        return $this->morphMany(Lists::class, 'lister');
    }

    /**
     * Create a new list owned by this model.
     *
     * @param array<string, mixed> $attributes
     */
    public function createList(array $attributes): Lists
    {
        /** @var Lists $list */
        $list = $this->lists()->create($attributes);

        return $list;
    }

    /**
     * Update an existing list owned by this model.
     *
     * @param array<string, mixed> $attributes
     *
     * @throws ListOwnershipException If the list is not owned by this model.
     */
    public function updateList(Lists $list, array $attributes): Lists
    {
        $this->assertListOwnership($list);

        $list->update($attributes);

        return $list->fresh() ?? $list;
    }

    /**
     * Delete a list owned by this model.
     *
     * @throws ListOwnershipException If the list is not owned by this model.
     */
    public function deleteList(Lists $list): bool
    {
        $this->assertListOwnership($list);

        return (bool) $list->delete();
    }

    /**
     * Assert that the given list is owned by this model.
     *
     * @throws ListOwnershipException If the list is not owned by this model.
     */
    protected function assertListOwnership(Lists $list): void
    {
        if ($list->lister_id !== $this->getKey() || $list->lister_type !== $this->getMorphClass()) {
            throw ListOwnershipException::notOwner();
        }
    }
}
