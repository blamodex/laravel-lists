<?php

declare(strict_types=1);

namespace Blamodex\Lists\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $slug
 * @property string $name
 * @property int $lister_id
 * @property string $lister_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model $lister
 * @property-read Collection<int, ListItem> $items
 *
 * @method static Lists|null find(mixed $id)
 * @method static Lists create(array<string, mixed> $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder<Lists> query()
 */
class Lists extends Model
{
    use HasUuids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'lister_id',
        'lister_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lister_id' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Lists $list): void {
            if (empty($list->slug)) {
                $list->slug = Str::slug($list->name);
            }
        });
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        /** @var string $table */
        $table = config('lists.table_names.lists', 'lists');

        return $table;
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the owner of the list (polymorphic).
     *
     * @return MorphTo<Model, $this>
     */
    public function lister(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all items in the list.
     *
     * @return HasMany<ListItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ListItem::class, 'list_id');
    }

    /**
     * Add a single item to the list.
     */
    public function addItem(Model $listable): ListItem
    {
        /** @var ListItem $item */
        $item = $this->items()->firstOrCreate([
            'listable_id' => $listable->getKey(),
            'listable_type' => $listable->getMorphClass(),
        ]);

        return $item;
    }

    /**
     * Add multiple items to the list.
     *
     * @param array<int, Model> $listables
     * @return Collection<int, ListItem>
     */
    public function addItems(array $listables): Collection
    {
        $items = new Collection();

        foreach ($listables as $listable) {
            $items->push($this->addItem($listable));
        }

        return $items;
    }

    /**
     * Remove a single item from the list.
     */
    public function removeItem(Model $listable): bool
    {
        $deleted = $this->items()
            ->where('listable_id', $listable->getKey())
            ->where('listable_type', $listable->getMorphClass())
            ->delete();

        return $deleted > 0;
    }

    /**
     * Remove multiple items from the list.
     *
     * @param array<int, Model> $listables
     */
    public function removeItems(array $listables): int
    {
        $count = 0;

        foreach ($listables as $listable) {
            if ($this->removeItem($listable)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if an item is in the list.
     */
    public function hasItem(Model $listable): bool
    {
        return $this->items()
            ->where('listable_id', $listable->getKey())
            ->where('listable_type', $listable->getMorphClass())
            ->exists();
    }
}
