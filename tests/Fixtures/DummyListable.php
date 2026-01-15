<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Fixtures;

use Blamodex\Lists\Contracts\ListableInterface;
use Blamodex\Lists\Traits\Listable;
use Illuminate\Database\Eloquent\Model;

/**
 * Dummy model for testing listable functionality.
 *
 * This model simulates a Product, Article, or other content that can be added to lists.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Blamodex\Lists\Models\Lists> $lists
 *
 * @method static DummyListable|null find(mixed $id)
 * @method static DummyListable create(array<string, mixed> $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder<DummyListable> query()
 *
 * @implements ListableInterface<DummyListable>
 */
class DummyListable extends Model implements ListableInterface
{
    use Listable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dummy_listables';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];
}
