<?php

declare(strict_types=1);

namespace Blamodex\Lists\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $list_id
 * @property int $listable_id
 * @property string $listable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Lists $list
 * @property-read Model $listable
 */
class ListItem extends Model
{
    use HasUuids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'list_id',
        'listable_id',
        'listable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'list_id' => 'integer',
        'listable_id' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ListItem $item): void {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('lists.table_names.list_items', 'list_items');
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
     * Get the list that owns this item.
     *
     * @return BelongsTo<Lists, ListItem>
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(Lists::class, 'list_id');
    }

    /**
     * Get the listable model (polymorphic).
     *
     * @return MorphTo<Model, ListItem>
     */
    public function listable(): MorphTo
    {
        return $this->morphTo();
    }
}
