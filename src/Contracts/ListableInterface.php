<?php

declare(strict_types=1);

namespace Blamodex\Lists\Contracts;

use Blamodex\Lists\Models\ListItem;
use Blamodex\Lists\Models\Lists;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Interface for models that can be added to lists.
 *
 * Implement this interface on models that should be listable,
 * such as Product, Article, or any other content model.
 */
interface ListableInterface
{
    /**
     * Get all lists that contain this model.
     *
     * @return MorphToMany<Lists, $this>
     */
    public function lists(): MorphToMany;

    /**
     * Get all list items for this model.
     *
     * @return Collection<int, ListItem>
     */
    public function listItems(): Collection;

    /**
     * Check if this model is in a specific list.
     */
    public function isInList(Lists $list): bool;
}
