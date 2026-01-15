<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Unit;

use Blamodex\Lists\Models\Lists;
use Blamodex\Lists\Tests\Fixtures\DummyListable;
use Blamodex\Lists\Tests\Fixtures\DummyListOwner;
use Blamodex\Lists\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use InvalidArgumentException;

class HasListsTraitTest extends TestCase
{
    private DummyListOwner $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = DummyListOwner::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function testListsRelationshipReturnsMorphMany(): void
    {
        $this->assertInstanceOf(MorphMany::class, $this->owner->lists());
    }

    public function testListsRelationshipReturnsEmptyCollectionByDefault(): void
    {
        $this->assertCount(0, $this->owner->lists);
    }

    public function testListsRelationshipReturnsOwnedLists(): void
    {
        Lists::create([
            'name' => 'List 1',
            'lister_id' => $this->owner->id,
            'lister_type' => $this->owner->getMorphClass(),
        ]);

        Lists::create([
            'name' => 'List 2',
            'lister_id' => $this->owner->id,
            'lister_type' => $this->owner->getMorphClass(),
        ]);

        $this->assertCount(2, $this->owner->fresh()->lists);
    }

    public function testCreateListMethod(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $this->assertInstanceOf(Lists::class, $list);
        $this->assertEquals('My Favorites', $list->name);
        $this->assertEquals($this->owner->id, $list->lister_id);
        $this->assertEquals($this->owner->getMorphClass(), $list->lister_type);
    }

    public function testCreateListMethodWithSlug(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
            'slug' => 'custom-favorites',
        ]);

        $this->assertEquals('custom-favorites', $list->slug);
    }

    public function testCreateListMethodAutoGeneratesSlug(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorite Products',
        ]);

        $this->assertEquals('my-favorite-products', $list->slug);
    }

    public function testCreateListMethodPersistsToDatabase(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'My Favorites',
            'lister_id' => $this->owner->id,
            'lister_type' => DummyListOwner::class,
        ]);
    }

    public function testUpdateListMethod(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $updatedList = $this->owner->updateList($list, [
            'name' => 'Updated Favorites',
        ]);

        $this->assertInstanceOf(Lists::class, $updatedList);
        $this->assertEquals('Updated Favorites', $updatedList->name);
    }

    public function testUpdateListMethodPersistsToDatabase(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $this->owner->updateList($list, [
            'name' => 'Updated Favorites',
        ]);

        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'Updated Favorites',
        ]);
    }

    public function testUpdateListMethodDoesNotChangeSlug(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $originalSlug = $list->slug;

        $updatedList = $this->owner->updateList($list, [
            'name' => 'Updated Favorites',
        ]);

        $this->assertEquals($originalSlug, $updatedList->slug);
    }

    public function testUpdateListThrowsExceptionWhenNotOwned(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $list = $otherOwner->createList([
            'name' => 'Other List',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided list is not owned by this model.');

        $this->owner->updateList($list, [
            'name' => 'Hacked List',
        ]);
    }

    public function testDeleteListMethod(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $listId = $list->id;

        $result = $this->owner->deleteList($list);

        $this->assertTrue($result);
        $this->assertSoftDeleted('lists', ['id' => $listId]);
    }

    public function testDeleteListMethodSoftDeletes(): void
    {
        $list = $this->owner->createList([
            'name' => 'My Favorites',
        ]);

        $listId = $list->id;

        $this->owner->deleteList($list);

        $this->assertNull(Lists::find($listId));
        $this->assertNotNull(Lists::withTrashed()->find($listId));
    }

    public function testDeleteListThrowsExceptionWhenNotOwned(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $list = $otherOwner->createList([
            'name' => 'Other List',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided list is not owned by this model.');

        $this->owner->deleteList($list);
    }

    public function testOwnerCanManageMultipleLists(): void
    {
        $list1 = $this->owner->createList(['name' => 'List 1']);
        $list2 = $this->owner->createList(['name' => 'List 2']);
        $list3 = $this->owner->createList(['name' => 'List 3']);

        $this->assertCount(3, $this->owner->fresh()->lists);

        $this->owner->updateList($list2, ['name' => 'Updated List 2']);
        $this->owner->deleteList($list3);

        $this->assertCount(2, $this->owner->fresh()->lists);
        $this->assertEquals('Updated List 2', $list2->fresh()->name);
    }

    public function testListsRelationshipDoesNotIncludeOtherOwnersLists(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $this->owner->createList(['name' => 'My List']);
        $otherOwner->createList(['name' => 'Other List']);

        $this->assertCount(1, $this->owner->fresh()->lists);
        $this->assertCount(1, $otherOwner->fresh()->lists);
        $this->assertEquals('My List', $this->owner->lists->first()->name);
        $this->assertEquals('Other List', $otherOwner->lists->first()->name);
    }

    public function testOwnershipCheckFailsWithDifferentListerType(): void
    {
        // Create a list with the same ID but different type
        $list = Lists::create([
            'name' => 'Test List',
            'lister_id' => $this->owner->id,
            'lister_type' => 'DifferentModel',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided list is not owned by this model.');

        $this->owner->updateList($list, ['name' => 'Updated']);
    }

    public function testOwnershipCheckFailsWithDifferentListerId(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        // Create a list with different owner ID but same type
        $list = Lists::create([
            'name' => 'Test List',
            'lister_id' => $otherOwner->id,
            'lister_type' => $this->owner->getMorphClass(),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided list is not owned by this model.');

        $this->owner->deleteList($list);
    }

    public function testCreateListReturnsListWithCorrectRelationship(): void
    {
        $list = $this->owner->createList(['name' => 'My List']);

        $this->assertInstanceOf(DummyListOwner::class, $list->lister);
        $this->assertEquals($this->owner->id, $list->lister->id);
    }

    public function testMultipleOwnersCanHaveListsWithSameName(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $list1 = $this->owner->createList(['name' => 'Favorites']);
        $list2 = $otherOwner->createList(['name' => 'Favorites']);

        $this->assertEquals('Favorites', $list1->name);
        $this->assertEquals('Favorites', $list2->name);
        $this->assertNotEquals($list1->id, $list2->id);
    }

    public function testUpdateListReturnsRefreshedModel(): void
    {
        $list = $this->owner->createList(['name' => 'My List']);

        $updatedList = $this->owner->updateList($list, ['name' => 'Updated Name']);

        // The returned model should have the updated name
        $this->assertEquals('Updated Name', $updatedList->name);
    }

    public function testDeleteListRemovesFromOwnerCollection(): void
    {
        $list = $this->owner->createList(['name' => 'My List']);

        $this->assertCount(1, $this->owner->fresh()->lists);

        $this->owner->deleteList($list);

        $this->assertCount(0, $this->owner->fresh()->lists);
    }
}
