<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Unit;

use Blamodex\Lists\Models\ListItem;
use Blamodex\Lists\Models\Lists;
use Blamodex\Lists\Services\ListService;
use Blamodex\Lists\Tests\Fixtures\DummyListable;
use Blamodex\Lists\Tests\Fixtures\DummyListOwner;
use Blamodex\Lists\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class ListServiceTest extends TestCase
{
    private ListService $service;

    private DummyListOwner $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListService();
        $this->owner = DummyListOwner::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    // ==========================================
    // create() method tests
    // ==========================================

    public function testCreateMethodCreatesListWithCorrectAttributes(): void
    {
        $list = $this->service->create($this->owner, [
            'name' => 'My Favorites',
        ]);

        $this->assertInstanceOf(Lists::class, $list);
        $this->assertEquals('My Favorites', $list->name);
        $this->assertEquals($this->owner->id, $list->lister_id);
        $this->assertEquals($this->owner->getMorphClass(), $list->lister_type);
    }

    public function testCreateMethodPersistsToDatabase(): void
    {
        $list = $this->service->create($this->owner, [
            'name' => 'My Favorites',
        ]);

        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'My Favorites',
            'lister_id' => $this->owner->id,
            'lister_type' => DummyListOwner::class,
        ]);
    }

    public function testCreateMethodWithCustomSlug(): void
    {
        $list = $this->service->create($this->owner, [
            'name' => 'My Favorites',
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('custom-slug', $list->slug);
    }

    public function testCreateMethodAutoGeneratesSlug(): void
    {
        $list = $this->service->create($this->owner, [
            'name' => 'My Favorite Products',
        ]);

        $this->assertEquals('my-favorite-products', $list->slug);
    }

    public function testCreateMethodAutoGeneratesUuid(): void
    {
        $list = $this->service->create($this->owner, [
            'name' => 'My Favorites',
        ]);

        $this->assertNotEmpty($list->uuid);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($list->uuid));
    }

    public function testCreateMethodThrowsExceptionWhenNameMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "name" attribute is required to create a list.');

        $this->service->create($this->owner, []);
    }

    public function testCreateMethodThrowsExceptionWhenNameEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "name" attribute is required to create a list.');

        $this->service->create($this->owner, ['name' => '']);
    }

    public function testCreateMethodWorksWithDifferentOwnerTypes(): void
    {
        $owner1 = DummyListOwner::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $owner2 = DummyListOwner::create(['name' => 'User 2', 'email' => 'user2@example.com']);

        $list1 = $this->service->create($owner1, ['name' => 'List 1']);
        $list2 = $this->service->create($owner2, ['name' => 'List 2']);

        $this->assertEquals($owner1->id, $list1->lister_id);
        $this->assertEquals($owner2->id, $list2->lister_id);
    }

    // ==========================================
    // update() method tests
    // ==========================================

    public function testUpdateMethodUpdatesListAttributes(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'Original Name']);

        $updated = $this->service->update($list, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function testUpdateMethodPersistsChangesToDatabase(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'Original Name']);

        $this->service->update($list, ['name' => 'Updated Name']);

        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'Updated Name',
        ]);
    }

    public function testUpdateMethodReturnsRefreshedModel(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'Original Name']);

        $updated = $this->service->update($list, ['name' => 'Updated Name']);

        $this->assertInstanceOf(Lists::class, $updated);
        $this->assertEquals('Updated Name', $updated->name);
    }

    public function testUpdateMethodDoesNotChangeSlug(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'Original Name']);
        $originalSlug = $list->slug;

        $this->service->update($list, ['name' => 'Updated Name']);

        $this->assertEquals($originalSlug, $list->fresh()->slug);
    }

    public function testUpdateMethodCanUpdateMultipleAttributes(): void
    {
        $list = $this->service->create($this->owner, [
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        $this->service->update($list, [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
        ]);

        $fresh = $list->fresh();
        $this->assertEquals('Updated Name', $fresh->name);
        $this->assertEquals('updated-slug', $fresh->slug);
    }

    // ==========================================
    // delete() method tests
    // ==========================================

    public function testDeleteMethodSoftDeletesList(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'To Delete']);
        $listId = $list->id;

        $result = $this->service->delete($list);

        $this->assertTrue($result);
        $this->assertSoftDeleted('lists', ['id' => $listId]);
    }

    public function testDeleteMethodRemovesListFromQueryResults(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'To Delete']);
        $listId = $list->id;

        $this->service->delete($list);

        $this->assertNull(Lists::find($listId));
    }

    public function testDeleteMethodAllowsRecoveryWithTrashed(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'To Delete']);
        $listId = $list->id;

        $this->service->delete($list);

        $this->assertNotNull(Lists::withTrashed()->find($listId));
    }

    public function testDeleteMethodReturnsBool(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'To Delete']);

        $result = $this->service->delete($list);

        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    // ==========================================
    // addItem() method tests
    // ==========================================

    public function testAddItemMethodAddsItemToList(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $item = $this->service->addItem($list, $listable);

        $this->assertInstanceOf(ListItem::class, $item);
        $this->assertEquals($list->id, $item->list_id);
        $this->assertEquals($listable->id, $item->listable_id);
        $this->assertEquals($listable->getMorphClass(), $item->listable_type);
    }

    public function testAddItemMethodPersistsToDatabase(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $this->service->addItem($list, $listable);

        $this->assertDatabaseHas('list_items', [
            'list_id' => $list->id,
            'listable_id' => $listable->id,
            'listable_type' => DummyListable::class,
        ]);
    }

    public function testAddItemMethodDoesNotDuplicateItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $item1 = $this->service->addItem($list, $listable);
        $item2 = $this->service->addItem($list, $listable);

        $this->assertEquals($item1->id, $item2->id);
        $this->assertCount(1, $list->fresh()->items);
    }

    public function testAddItemMethodThrowsExceptionForUnsavedModel(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = new DummyListable(['name' => 'Unsaved Product']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The listable model must be saved before adding to a list.');

        $this->service->addItem($list, $listable);
    }

    public function testAddItemMethodGeneratesUuidForItem(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $item = $this->service->addItem($list, $listable);

        $this->assertNotEmpty($item->uuid);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($item->uuid));
    }

    // ==========================================
    // addItems() method tests
    // ==========================================

    public function testAddItemsMethodAddsMultipleItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $listable3 = DummyListable::create(['name' => 'Product 3']);

        $items = $this->service->addItems($list, [$listable1, $listable2, $listable3]);

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(3, $items);
        $this->assertCount(3, $list->fresh()->items);
    }

    public function testAddItemsMethodReturnsCollectionOfListItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);

        $items = $this->service->addItems($list, [$listable1, $listable2]);

        foreach ($items as $item) {
            $this->assertInstanceOf(ListItem::class, $item);
        }
    }

    public function testAddItemsMethodHandlesEmptyArray(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);

        $items = $this->service->addItems($list, []);

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(0, $items);
    }

    public function testAddItemsMethodDoesNotDuplicateExistingItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);

        // Add first item
        $this->service->addItem($list, $listable1);

        // Add both items (listable1 already exists)
        $items = $this->service->addItems($list, [$listable1, $listable2]);

        $this->assertCount(2, $items);
        $this->assertCount(2, $list->fresh()->items);
    }

    public function testAddItemsMethodThrowsExceptionForUnsavedModel(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = new DummyListable(['name' => 'Unsaved Product']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The listable model must be saved before adding to a list.');

        $this->service->addItems($list, [$listable1, $listable2]);
    }

    // ==========================================
    // removeItem() method tests
    // ==========================================

    public function testRemoveItemMethodRemovesItemFromList(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);

        $result = $this->service->removeItem($list, $listable);

        $this->assertTrue($result);
        $this->assertCount(0, $list->fresh()->items);
    }

    public function testRemoveItemMethodReturnsFalseWhenItemNotFound(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $result = $this->service->removeItem($list, $listable);

        $this->assertFalse($result);
    }

    public function testRemoveItemMethodDeletesFromDatabase(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);

        $this->service->removeItem($list, $listable);

        $this->assertDatabaseMissing('list_items', [
            'list_id' => $list->id,
            'listable_id' => $listable->id,
            'listable_type' => DummyListable::class,
            'deleted_at' => null,
        ]);
    }

    public function testRemoveItemMethodOnlyRemovesSpecificItem(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $this->service->addItems($list, [$listable1, $listable2]);

        $this->service->removeItem($list, $listable1);

        $this->assertCount(1, $list->fresh()->items);
        $this->assertTrue($this->service->hasItem($list, $listable2));
        $this->assertFalse($this->service->hasItem($list, $listable1));
    }

    // ==========================================
    // removeItems() method tests
    // ==========================================

    public function testRemoveItemsMethodRemovesMultipleItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $listable3 = DummyListable::create(['name' => 'Product 3']);
        $this->service->addItems($list, [$listable1, $listable2, $listable3]);

        $count = $this->service->removeItems($list, [$listable1, $listable2]);

        $this->assertEquals(2, $count);
        $this->assertCount(1, $list->fresh()->items);
    }

    public function testRemoveItemsMethodReturnsCountOfRemovedItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $listable3 = DummyListable::create(['name' => 'Product 3']);
        $this->service->addItem($list, $listable1);

        // Try to remove 3 items, but only 1 exists
        $count = $this->service->removeItems($list, [$listable1, $listable2, $listable3]);

        $this->assertEquals(1, $count);
    }

    public function testRemoveItemsMethodHandlesEmptyArray(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);

        $count = $this->service->removeItems($list, []);

        $this->assertEquals(0, $count);
        $this->assertCount(1, $list->fresh()->items);
    }

    public function testRemoveItemsMethodReturnsZeroWhenNoItemsRemoved(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);

        // Items not in list
        $count = $this->service->removeItems($list, [$listable1, $listable2]);

        $this->assertEquals(0, $count);
    }

    // ==========================================
    // hasItem() method tests
    // ==========================================

    public function testHasItemMethodReturnsTrueWhenItemExists(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);

        $this->assertTrue($this->service->hasItem($list, $listable));
    }

    public function testHasItemMethodReturnsFalseWhenItemNotExists(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $this->assertFalse($this->service->hasItem($list, $listable));
    }

    public function testHasItemMethodReturnsFalseAfterRemoval(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);
        $this->assertTrue($this->service->hasItem($list, $listable));

        $this->service->removeItem($list, $listable);

        $this->assertFalse($this->service->hasItem($list, $listable));
    }

    public function testHasItemMethodDistinguishesBetweenLists(): void
    {
        $list1 = $this->service->create($this->owner, ['name' => 'List 1']);
        $list2 = $this->service->create($this->owner, ['name' => 'List 2']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list1, $listable);

        $this->assertTrue($this->service->hasItem($list1, $listable));
        $this->assertFalse($this->service->hasItem($list2, $listable));
    }

    // ==========================================
    // getItems() method tests
    // ==========================================

    public function testGetItemsMethodReturnsCollection(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);

        $items = $this->service->getItems($list);

        $this->assertInstanceOf(Collection::class, $items);
    }

    public function testGetItemsMethodReturnsEmptyCollectionForEmptyList(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);

        $items = $this->service->getItems($list);

        $this->assertCount(0, $items);
    }

    public function testGetItemsMethodReturnsAllListItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $listable3 = DummyListable::create(['name' => 'Product 3']);
        $this->service->addItems($list, [$listable1, $listable2, $listable3]);

        $items = $this->service->getItems($list);

        $this->assertCount(3, $items);
        foreach ($items as $item) {
            $this->assertInstanceOf(ListItem::class, $item);
        }
    }

    public function testGetItemsMethodReturnsListItemsNotListables(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);

        $items = $this->service->getItems($list);

        $this->assertInstanceOf(ListItem::class, $items->first());
        $this->assertNotInstanceOf(DummyListable::class, $items->first());
    }

    // ==========================================
    // clearItems() method tests
    // ==========================================

    public function testClearItemsMethodRemovesAllItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $listable3 = DummyListable::create(['name' => 'Product 3']);
        $this->service->addItems($list, [$listable1, $listable2, $listable3]);

        $count = $this->service->clearItems($list);

        $this->assertEquals(3, $count);
        $this->assertCount(0, $list->fresh()->items);
    }

    public function testClearItemsMethodReturnsCountOfRemovedItems(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable1 = DummyListable::create(['name' => 'Product 1']);
        $listable2 = DummyListable::create(['name' => 'Product 2']);
        $this->service->addItems($list, [$listable1, $listable2]);

        $count = $this->service->clearItems($list);

        $this->assertEquals(2, $count);
    }

    public function testClearItemsMethodReturnsZeroForEmptyList(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);

        $count = $this->service->clearItems($list);

        $this->assertEquals(0, $count);
    }

    public function testClearItemsMethodDeletesFromDatabase(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);
        $this->service->addItem($list, $listable);

        $this->service->clearItems($list);

        $this->assertDatabaseMissing('list_items', [
            'list_id' => $list->id,
            'deleted_at' => null,
        ]);
    }

    // ==========================================
    // Edge cases and integration tests
    // ==========================================

    public function testServiceCanHandleSameItemInMultipleLists(): void
    {
        $list1 = $this->service->create($this->owner, ['name' => 'List 1']);
        $list2 = $this->service->create($this->owner, ['name' => 'List 2']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $this->service->addItem($list1, $listable);
        $this->service->addItem($list2, $listable);

        $this->assertTrue($this->service->hasItem($list1, $listable));
        $this->assertTrue($this->service->hasItem($list2, $listable));
        $this->assertCount(1, $list1->fresh()->items);
        $this->assertCount(1, $list2->fresh()->items);
    }

    public function testServiceCanHandleMultipleOwners(): void
    {
        $owner1 = DummyListOwner::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $owner2 = DummyListOwner::create(['name' => 'User 2', 'email' => 'user2@example.com']);

        $list1 = $this->service->create($owner1, ['name' => 'User 1 List']);
        $list2 = $this->service->create($owner2, ['name' => 'User 2 List']);

        $this->assertEquals($owner1->id, $list1->lister_id);
        $this->assertEquals($owner2->id, $list2->lister_id);
        $this->assertNotEquals($list1->lister_id, $list2->lister_id);
    }

    public function testFullWorkflow(): void
    {
        // Create a list
        $list = $this->service->create($this->owner, ['name' => 'Shopping List']);
        $this->assertInstanceOf(Lists::class, $list);

        // Add items
        $product1 = DummyListable::create(['name' => 'Apples']);
        $product2 = DummyListable::create(['name' => 'Bananas']);
        $product3 = DummyListable::create(['name' => 'Oranges']);

        $this->service->addItems($list, [$product1, $product2, $product3]);
        $this->assertCount(3, $this->service->getItems($list));

        // Check item existence
        $this->assertTrue($this->service->hasItem($list, $product1));

        // Remove an item
        $this->service->removeItem($list, $product2);
        $this->assertFalse($this->service->hasItem($list, $product2));
        $this->assertCount(2, $this->service->getItems($list->fresh()));

        // Update list
        $this->service->update($list, ['name' => 'Grocery List']);
        $this->assertEquals('Grocery List', $list->fresh()->name);

        // Clear items (need to refresh list first)
        $list = $list->fresh();
        $this->service->clearItems($list);
        $this->assertCount(0, $this->service->getItems($list->fresh()));

        // Delete list
        $result = $this->service->delete($list);
        $this->assertTrue($result);
        $this->assertNull(Lists::find($list->id));
    }

    public function testServiceIsInstantiable(): void
    {
        $service = new ListService();

        $this->assertInstanceOf(ListService::class, $service);
    }

    public function testRemoveItemFromDifferentListDoesNotAffectOriginal(): void
    {
        $list1 = $this->service->create($this->owner, ['name' => 'List 1']);
        $list2 = $this->service->create($this->owner, ['name' => 'List 2']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $this->service->addItem($list1, $listable);
        $this->service->addItem($list2, $listable);

        // Remove from list2
        $this->service->removeItem($list2, $listable);

        // Should still be in list1
        $this->assertTrue($this->service->hasItem($list1, $listable));
        $this->assertFalse($this->service->hasItem($list2, $listable));
    }

    public function testDeletingListDoesNotAffectOtherLists(): void
    {
        $list1 = $this->service->create($this->owner, ['name' => 'List 1']);
        $list2 = $this->service->create($this->owner, ['name' => 'List 2']);

        $this->service->delete($list1);

        $this->assertNull(Lists::find($list1->id));
        $this->assertNotNull(Lists::find($list2->id));
    }

    public function testCreateMethodSetsTimestamps(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);

        $this->assertNotNull($list->created_at);
        $this->assertNotNull($list->updated_at);
    }

    public function testAddItemMethodSetsTimestampsOnListItem(): void
    {
        $list = $this->service->create($this->owner, ['name' => 'My List']);
        $listable = DummyListable::create(['name' => 'Product 1']);

        $item = $this->service->addItem($list, $listable);

        $this->assertNotNull($item->created_at);
        $this->assertNotNull($item->updated_at);
    }
}
