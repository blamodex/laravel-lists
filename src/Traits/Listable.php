<?php

declare(strict_types=1);

namespace Blamodex\Lists\Traits;

use Blamodex\Lists\Models\ListItem;
use Blamodex\Lists\Models\Lists;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Trait for models that can be added to lists.
 *
 * Use this trait on models that should be listable,
 * such as Product, Article, or any other content model.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Listable
{
    /**
     * Get all lists that contain this model.
     *
     * @return MorphToMany<Lists, $this>
     */
    public function lists(): MorphToMany
    {
        return $this->morphToMany(
            Lists::class,
            'listable',
            config('lists.table_names.list_items', 'list_items'),
            'listable_id',
            'list_id'
        )->wherePivotNull('deleted_at')->withTimestamps();
    }

    /**
     * Get all list items for this model.
     *
     * @return Collection<int, ListItem>
     */
    public function listItems(): Collection
    {
        /** @var Collection<int, ListItem> $items */
        $items = ListItem::query()
            ->where('listable_id', $this->getKey())
            ->where('listable_type', $this->getMorphClass())
            ->get();

        return $items;
    }

    /**
     * Check if this model is in a specific list.
     */
    public function isInList(Lists $list): bool
    {
        return ListItem::query()
            ->where('list_id', $list->getKey())
            ->where('listable_id', $this->getKey())
            ->where('listable_type', $this->getMorphClass())
            ->exists();
    }
}
