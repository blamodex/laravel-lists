<?php

declare(strict_types=1);

namespace Blamodex\Lists\Services;

use Blamodex\Lists\Exceptions\InvalidListableException;
use Blamodex\Lists\Exceptions\InvalidListAttributeException;
use Blamodex\Lists\Models\ListItem;
use Blamodex\Lists\Models\Lists;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Service class for managing lists and list items.
 *
 * Provides a centralized service layer for create, update, delete operations
 * on lists, as well as adding and removing items from lists.
 */
class ListService
{
    /**
     * Create a new list for the given owner.
     *
     * @param Model $lister The model that will own the list.
     * @param array<string, mixed> $attributes The list attributes (name, slug, etc.).
     *
     * @throws InvalidListAttributeException If required attributes are missing.
     */
    public function create(Model $lister, array $attributes): Lists
    {
        if (empty($attributes['name'])) {
            throw InvalidListAttributeException::missingAttribute('name');
        }

        $list = new Lists();
        $list->fill($attributes);
        /** @var int $listerId */
        $listerId = $lister->getKey();
        $list->lister_id = $listerId;
        $list->lister_type = $lister->getMorphClass();
        $list->save();

        return $list;
    }

    /**
     * Update an existing list.
     *
     * @param Lists $list The list to update.
     * @param array<string, mixed> $attributes The attributes to update.
     */
    public function update(Lists $list, array $attributes): Lists
    {
        $list->update($attributes);

        return $list->fresh() ?? $list;
    }

    /**
     * Delete a list (soft delete).
     *
     * @param Lists $list The list to delete.
     */
    public function delete(Lists $list): bool
    {
        return (bool) $list->delete();
    }

    /**
     * Add a single item to a list.
     *
     * @param Lists $list The list to add the item to.
     * @param Model $listable The model to add to the list.
     *
     * @throws InvalidListableException If the listable model has no primary key.
     */
    public function addItem(Lists $list, Model $listable): ListItem
    {
        $key = $listable->getKey();

        if ($key === null) {
            throw InvalidListableException::unsavedModel();
        }

        /** @var ListItem $item */
        $item = $list->items()->firstOrCreate([
            'listable_id' => $key,
            'listable_type' => $listable->getMorphClass(),
        ]);

        return $item;
    }

    /**
     * Add multiple items to a list.
     *
     * @param Lists $list The list to add items to.
     * @param array<int, Model> $listables The models to add to the list.
     *
     * @return Collection<int, ListItem> Collection of created/existing list items.
     *
     * @throws InvalidListableException If any listable model has no primary key.
     */
    public function addItems(Lists $list, array $listables): Collection
    {
        $items = new Collection();

        foreach ($listables as $listable) {
            $items->push($this->addItem($list, $listable));
        }

        return $items;
    }

    /**
     * Remove a single item from a list.
     *
     * @param Lists $list The list to remove the item from.
     * @param Model $listable The model to remove from the list.
     *
     * @return bool True if the item was removed, false if it wasn't in the list.
     */
    public function removeItem(Lists $list, Model $listable): bool
    {
        $deleted = $list->items()
            ->where('listable_id', $listable->getKey())
            ->where('listable_type', $listable->getMorphClass())
            ->delete();

        return $deleted > 0;
    }

    /**
     * Remove multiple items from a list.
     *
     * @param Lists $list The list to remove items from.
     * @param array<int, Model> $listables The models to remove from the list.
     *
     * @return int The number of items successfully removed.
     */
    public function removeItems(Lists $list, array $listables): int
    {
        $count = 0;

        foreach ($listables as $listable) {
            if ($this->removeItem($list, $listable)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if an item exists in a list.
     *
     * @param Lists $list The list to check.
     * @param Model $listable The model to check for.
     */
    public function hasItem(Lists $list, Model $listable): bool
    {
        return $list->items()
            ->where('listable_id', $listable->getKey())
            ->where('listable_type', $listable->getMorphClass())
            ->exists();
    }

    /**
     * Get all items in a list.
     *
     * @param Lists $list The list to get items from.
     *
     * @return Collection<int, ListItem> Collection of list items.
     */
    public function getItems(Lists $list): Collection
    {
        return $list->items;
    }

    /**
     * Clear all items from a list.
     *
     * @param Lists $list The list to clear.
     *
     * @return int The number of items removed.
     */
    public function clearItems(Lists $list): int
    {
        /** @var int $count */
        $count = $list->items()->delete();

        return $count;
    }
}
