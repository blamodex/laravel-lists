<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Integration;

use Blamodex\Lists\Exceptions\ListOwnershipException;
use Blamodex\Lists\Models\ListItem;
use Blamodex\Lists\Models\Lists;
use Blamodex\Lists\Services\ListService;
use Blamodex\Lists\Tests\Fixtures\DummyListable;
use Blamodex\Lists\Tests\Fixtures\DummyListOwner;
use Blamodex\Lists\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Integration tests for the Laravel Lists package.
 *
 * These tests verify that all components work together correctly
 * and that the package functions as expected in real-world scenarios.
 */
class ListsIntegrationTest extends TestCase
{
    private ListService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListService();
    }

    // ==========================================
    // Migration tests
    // ==========================================

    public function testMigrationsRunSuccessfully(): void
    {
        $this->assertTrue(Schema::hasTable('lists'));
        $this->assertTrue(Schema::hasTable('list_items'));
    }

    public function testListsTableHasCorrectColumns(): void
    {
        $this->assertTrue(Schema::hasColumn('lists', 'id'));
        $this->assertTrue(Schema::hasColumn('lists', 'uuid'));
        $this->assertTrue(Schema::hasColumn('lists', 'slug'));
        $this->assertTrue(Schema::hasColumn('lists', 'name'));
        $this->assertTrue(Schema::hasColumn('lists', 'lister_id'));
        $this->assertTrue(Schema::hasColumn('lists', 'lister_type'));
        $this->assertTrue(Schema::hasColumn('lists', 'created_at'));
        $this->assertTrue(Schema::hasColumn('lists', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('lists', 'deleted_at'));
    }

    public function testListItemsTableHasCorrectColumns(): void
    {
        $this->assertTrue(Schema::hasColumn('list_items', 'id'));
        $this->assertTrue(Schema::hasColumn('list_items', 'uuid'));
        $this->assertTrue(Schema::hasColumn('list_items', 'list_id'));
        $this->assertTrue(Schema::hasColumn('list_items', 'listable_id'));
        $this->assertTrue(Schema::hasColumn('list_items', 'listable_type'));
        $this->assertTrue(Schema::hasColumn('list_items', 'created_at'));
        $this->assertTrue(Schema::hasColumn('list_items', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('list_items', 'deleted_at'));
    }

    // ==========================================
    // Full workflow tests
    // ==========================================

    public function testFullWorkflowCreateAddRemoveDelete(): void
    {
        // 1. Create owner
        $owner = DummyListOwner::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. Create a list using the trait
        $list = $owner->createList(['name' => 'My Wishlist']);

        $this->assertInstanceOf(Lists::class, $list);
        $this->assertEquals('My Wishlist', $list->name);
        $this->assertEquals('my-wishlist', $list->slug);
        $this->assertNotEmpty($list->uuid);
        $this->assertEquals($owner->id, $list->lister_id);

        // 3. Create listable items
        $product1 = DummyListable::create(['name' => 'iPhone']);
        $product2 = DummyListable::create(['name' => 'MacBook']);
        $product3 = DummyListable::create(['name' => 'iPad']);

        // 4. Add items to the list
        $list->addItem($product1);
        $list->addItems([$product2, $product3]);

        $this->assertCount(3, $list->fresh()->items);
        $this->assertTrue($list->hasItem($product1));
        $this->assertTrue($list->hasItem($product2));
        $this->assertTrue($list->hasItem($product3));

        // 5. Verify items appear in listable's lists
        $this->assertTrue($product1->isInList($list));
        $this->assertTrue($product2->isInList($list));
        $this->assertCount(1, $product1->lists);

        // 6. Remove an item
        $result = $list->removeItem($product2);
        $this->assertTrue($result);
        $this->assertFalse($list->hasItem($product2));
        $this->assertCount(2, $list->fresh()->items);

        // 7. Update the list
        $owner->updateList($list, ['name' => 'My Updated Wishlist']);
        $this->assertEquals('My Updated Wishlist', $list->fresh()->name);

        // 8. Delete the list
        $owner->deleteList($list);
        $this->assertSoftDeleted('lists', ['id' => $list->id]);

        // 9. Verify list is gone from normal queries
        $this->assertNull(Lists::find($list->id));
        $this->assertCount(0, $owner->lists);

        // 10. Verify list can be recovered
        $this->assertNotNull(Lists::withTrashed()->find($list->id));
    }

    public function testFullWorkflowUsingService(): void
    {
        $owner = DummyListOwner::create([
            'name' => 'Service User',
            'email' => 'service@example.com',
        ]);

        // Create list via service
        $list = $this->service->create($owner, ['name' => 'Service List']);
        $this->assertInstanceOf(Lists::class, $list);

        // Add items via service
        $item1 = DummyListable::create(['name' => 'Item 1']);
        $item2 = DummyListable::create(['name' => 'Item 2']);

        $this->service->addItem($list, $item1);
        $this->service->addItems($list, [$item2]);

        $this->assertCount(2, $this->service->getItems($list));

        // Check item presence via service
        $this->assertTrue($this->service->hasItem($list, $item1));

        // Remove via service
        $this->service->removeItem($list, $item1);
        $this->assertFalse($this->service->hasItem($list, $item1));

        // Update via service
        $this->service->update($list, ['name' => 'Updated Service List']);
        $this->assertEquals('Updated Service List', $list->fresh()->name);

        // Clear all items
        $count = $this->service->clearItems($list->fresh());
        $this->assertEquals(1, $count);

        // Delete via service
        $this->service->delete($list);
        $this->assertNull(Lists::find($list->id));
    }

    // ==========================================
    // Multiple owners tests
    // ==========================================

    public function testMultipleOwnersWithMultipleLists(): void
    {
        // Create owners
        $user1 = DummyListOwner::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $user2 = DummyListOwner::create(['name' => 'User 2', 'email' => 'user2@example.com']);
        $user3 = DummyListOwner::create(['name' => 'User 3', 'email' => 'user3@example.com']);

        // Each user creates multiple lists
        $user1List1 = $user1->createList(['name' => 'User 1 Favorites']);
        $user1List2 = $user1->createList(['name' => 'User 1 Wishlist']);

        $user2List1 = $user2->createList(['name' => 'User 2 Favorites']);
        $user2List2 = $user2->createList(['name' => 'User 2 Wishlist']);
        $user2List3 = $user2->createList(['name' => 'User 2 Shopping']);

        $user3List1 = $user3->createList(['name' => 'User 3 Favorites']);

        // Verify each user only sees their own lists
        $this->assertCount(2, $user1->lists);
        $this->assertCount(3, $user2->lists);
        $this->assertCount(1, $user3->lists);

        // Verify list ownership
        $this->assertTrue($user1->lists->contains($user1List1));
        $this->assertTrue($user1->lists->contains($user1List2));
        $this->assertFalse($user1->lists->contains($user2List1));

        // Verify lister relationship
        $this->assertEquals($user1->id, $user1List1->lister->id);
        $this->assertEquals($user2->id, $user2List1->lister->id);
        $this->assertEquals($user3->id, $user3List1->lister->id);
    }

    public function testOwnersCannotModifyOtherOwnersLists(): void
    {
        $user1 = DummyListOwner::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $user2 = DummyListOwner::create(['name' => 'User 2', 'email' => 'user2@example.com']);

        $user1List = $user1->createList(['name' => 'User 1 List']);

        // User 2 should not be able to update User 1's list
        $this->expectException(ListOwnershipException::class);
        $this->expectExceptionMessage('The provided list is not owned by this model.');

        $user2->updateList($user1List, ['name' => 'Hacked List']);
    }

    public function testOwnersCannotDeleteOtherOwnersLists(): void
    {
        $user1 = DummyListOwner::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $user2 = DummyListOwner::create(['name' => 'User 2', 'email' => 'user2@example.com']);

        $user1List = $user1->createList(['name' => 'User 1 List']);

        // User 2 should not be able to delete User 1's list
        $this->expectException(ListOwnershipException::class);
        $this->expectExceptionMessage('The provided list is not owned by this model.');

        $user2->deleteList($user1List);
    }

    // ==========================================
    // Same item in multiple lists tests
    // ==========================================

    public function testSameItemInMultipleLists(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $product = DummyListable::create(['name' => 'Popular Product']);

        // Create multiple lists
        $favorites = $owner->createList(['name' => 'Favorites']);
        $wishlist = $owner->createList(['name' => 'Wishlist']);
        $compare = $owner->createList(['name' => 'Compare']);

        // Add the same product to all lists
        $favorites->addItem($product);
        $wishlist->addItem($product);
        $compare->addItem($product);

        // Verify product is in all lists
        $this->assertTrue($favorites->hasItem($product));
        $this->assertTrue($wishlist->hasItem($product));
        $this->assertTrue($compare->hasItem($product));

        // Verify product knows about all its lists
        $this->assertCount(3, $product->fresh()->lists);
        $this->assertTrue($product->isInList($favorites));
        $this->assertTrue($product->isInList($wishlist));
        $this->assertTrue($product->isInList($compare));

        // Remove from one list shouldn't affect others
        $favorites->removeItem($product);

        $this->assertFalse($favorites->hasItem($product));
        $this->assertTrue($wishlist->hasItem($product));
        $this->assertTrue($compare->hasItem($product));
        $this->assertCount(2, $product->fresh()->lists);
    }

    public function testSameItemInMultipleOwnersLists(): void
    {
        $user1 = DummyListOwner::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $user2 = DummyListOwner::create(['name' => 'User 2', 'email' => 'user2@example.com']);

        $product = DummyListable::create(['name' => 'Shared Product']);

        $user1List = $user1->createList(['name' => 'User 1 Favorites']);
        $user2List = $user2->createList(['name' => 'User 2 Favorites']);

        // Both users add the same product
        $user1List->addItem($product);
        $user2List->addItem($product);

        // Product should be in both lists
        $this->assertCount(2, $product->lists);
        $this->assertTrue($product->isInList($user1List));
        $this->assertTrue($product->isInList($user2List));

        // Verify list items are separate
        $this->assertCount(1, $user1List->items);
        $this->assertCount(1, $user2List->items);

        // Each list item should point to the correct list
        $user1ListItem = $user1List->items->first();
        $user2ListItem = $user2List->items->first();

        $this->assertEquals($user1List->id, $user1ListItem->list_id);
        $this->assertEquals($user2List->id, $user2ListItem->list_id);

        // Both list items should point to same product
        $this->assertEquals($product->id, $user1ListItem->listable_id);
        $this->assertEquals($product->id, $user2ListItem->listable_id);
    }

    // ==========================================
    // Polymorphic relationship tests
    // ==========================================

    public function testPolymorphicRelationshipsWorkCorrectly(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $list = $owner->createList(['name' => 'My List']);
        $product = DummyListable::create(['name' => 'Product']);

        $list->addItem($product);

        // Test lister polymorphic relationship
        $this->assertInstanceOf(DummyListOwner::class, $list->lister);
        $this->assertEquals($owner->id, $list->lister->id);
        $this->assertEquals(DummyListOwner::class, $list->lister_type);

        // Test listable polymorphic relationship
        $listItem = $list->items->first();
        $this->assertInstanceOf(DummyListable::class, $listItem->listable);
        $this->assertEquals($product->id, $listItem->listable->id);
        $this->assertEquals(DummyListable::class, $listItem->listable_type);
    }

    public function testMorphManyRelationshipFromOwner(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);

        $list1 = $owner->createList(['name' => 'List 1']);
        $list2 = $owner->createList(['name' => 'List 2']);

        $lists = $owner->lists;

        $this->assertInstanceOf(Collection::class, $lists);
        $this->assertCount(2, $lists);
        $this->assertTrue($lists->every(fn ($list) => $list instanceof Lists));
        $this->assertTrue($lists->every(fn ($list) => $list->lister_id === $owner->id));
        $this->assertTrue($lists->every(fn ($list) => $list->lister_type === DummyListOwner::class));
    }

    public function testMorphToManyRelationshipFromListable(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $product = DummyListable::create(['name' => 'Product']);

        $list1 = $owner->createList(['name' => 'List 1']);
        $list2 = $owner->createList(['name' => 'List 2']);

        $list1->addItem($product);
        $list2->addItem($product);

        $lists = $product->lists;

        $this->assertInstanceOf(Collection::class, $lists);
        $this->assertCount(2, $lists);
        $this->assertTrue($lists->every(fn ($list) => $list instanceof Lists));
    }

    public function testListItemsRelationshipFromListable(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $product = DummyListable::create(['name' => 'Product']);

        $list1 = $owner->createList(['name' => 'List 1']);
        $list2 = $owner->createList(['name' => 'List 2']);

        $list1->addItem($product);
        $list2->addItem($product);

        $listItems = $product->listItems();

        $this->assertInstanceOf(Collection::class, $listItems);
        $this->assertCount(2, $listItems);
        $this->assertTrue($listItems->every(fn ($item) => $item instanceof ListItem));
    }

    // ==========================================
    // Data integrity tests
    // ==========================================

    public function testUniqueConstraintPreventsduplicateListItems(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $list = $owner->createList(['name' => 'My List']);
        $product = DummyListable::create(['name' => 'Product']);

        // Add item first time
        $item1 = $list->addItem($product);

        // Add item second time (should return existing item)
        $item2 = $list->addItem($product);

        // Should be the same item
        $this->assertEquals($item1->id, $item2->id);

        // Only one item should exist
        $this->assertCount(1, $list->fresh()->items);

        // Verify in database
        $count = DB::table('list_items')
            ->where('list_id', $list->id)
            ->where('listable_id', $product->id)
            ->where('listable_type', DummyListable::class)
            ->whereNull('deleted_at')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function testCascadeDeleteOnListRemovesListItems(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $list = $owner->createList(['name' => 'My List']);

        $product1 = DummyListable::create(['name' => 'Product 1']);
        $product2 = DummyListable::create(['name' => 'Product 2']);

        $list->addItems([$product1, $product2]);

        $listId = $list->id;

        // Soft delete the list
        $owner->deleteList($list);

        // List items should still exist (soft deleted)
        $itemCount = ListItem::where('list_id', $listId)->count();
        $this->assertGreaterThan(0, $itemCount);
    }

    public function testSoftDeletedListsAreExcludedFromRelationships(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);

        $list1 = $owner->createList(['name' => 'List 1']);
        $list2 = $owner->createList(['name' => 'List 2']);

        $this->assertCount(2, $owner->lists);

        // Soft delete one list
        $owner->deleteList($list1);

        // Refresh the owner
        $owner = $owner->fresh();

        // Should only see one list
        $this->assertCount(1, $owner->lists);
        $this->assertEquals($list2->id, $owner->lists->first()->id);

        // But with trashed, should see both
        $this->assertCount(2, Lists::withTrashed()->where('lister_id', $owner->id)->get());
    }

    // ==========================================
    // Edge case tests
    // ==========================================

    public function testEmptyListBehavior(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $list = $owner->createList(['name' => 'Empty List']);

        $this->assertCount(0, $list->items);
        $this->assertInstanceOf(Collection::class, $list->items);

        $product = DummyListable::create(['name' => 'Product']);
        $this->assertFalse($list->hasItem($product));
    }

    public function testListableNotInAnyList(): void
    {
        $product = DummyListable::create(['name' => 'Lonely Product']);

        $this->assertCount(0, $product->lists);
        $this->assertInstanceOf(Collection::class, $product->lists);
        $this->assertCount(0, $product->listItems());

        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $list = $owner->createList(['name' => 'Some List']);

        $this->assertFalse($product->isInList($list));
    }

    public function testSlugUniquenessPerOwnerEnforced(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);

        $list1 = $owner->createList(['name' => 'My Favorites']);

        $this->assertNotEmpty($list1->slug);
        $this->assertEquals('my-favorites', $list1->slug);

        // Trying to create another list with the same auto-generated slug should fail
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        $owner->createList(['name' => 'My Favorites']); // Same name = same slug
    }

    public function testDifferentOwnersCanHaveSameSlug(): void
    {
        $owner1 = DummyListOwner::create(['name' => 'Owner 1', 'email' => 'owner1@example.com']);
        $owner2 = DummyListOwner::create(['name' => 'Owner 2', 'email' => 'owner2@example.com']);

        $list1 = $owner1->createList(['name' => 'My Favorites']);
        $list2 = $owner2->createList(['name' => 'My Favorites']);

        // Different owners can have the same slug
        $this->assertEquals('my-favorites', $list1->slug);
        $this->assertEquals('my-favorites', $list2->slug);
    }

    public function testLargeNumberOfItemsInList(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $list = $owner->createList(['name' => 'Big List']);

        $products = [];
        for ($i = 1; $i <= 100; $i++) {
            $products[] = DummyListable::create(['name' => "Product $i"]);
        }

        $list->addItems($products);

        $this->assertCount(100, $list->fresh()->items);

        // Verify random product is in list
        $this->assertTrue($list->hasItem($products[50]));

        // Remove half
        $toRemove = array_slice($products, 0, 50);
        $list->removeItems($toRemove);

        $this->assertCount(50, $list->fresh()->items);
    }

    public function testUuidUniquenessAcrossModels(): void
    {
        $owner = DummyListOwner::create(['name' => 'Owner', 'email' => 'owner@example.com']);

        $list1 = $owner->createList(['name' => 'List 1']);
        $list2 = $owner->createList(['name' => 'List 2']);

        $product = DummyListable::create(['name' => 'Product']);
        $item1 = $list1->addItem($product);
        $item2 = $list2->addItem($product);

        // All UUIDs should be unique
        $uuids = [
            $list1->uuid,
            $list2->uuid,
            $item1->uuid,
            $item2->uuid,
        ];

        $this->assertCount(4, array_unique($uuids));
    }
}
