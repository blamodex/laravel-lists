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
