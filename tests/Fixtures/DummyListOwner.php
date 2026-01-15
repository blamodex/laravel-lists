<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Fixtures;

use Blamodex\Lists\Contracts\HasListsInterface;
use Blamodex\Lists\Traits\HasLists;
use Illuminate\Database\Eloquent\Model;

/**
 * Dummy model for testing list ownership functionality.
 *
 * This model simulates a User or Team that can create and manage lists.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Blamodex\Lists\Models\Lists> $lists
 *
 * @method static DummyListOwner|null find(mixed $id)
 * @method static DummyListOwner create(array<string, mixed> $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder<DummyListOwner> query()
 *
 * @implements HasListsInterface<DummyListOwner>
 */
class DummyListOwner extends Model implements HasListsInterface
{
    use HasLists;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dummy_list_owners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];
}
