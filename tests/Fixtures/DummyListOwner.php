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
