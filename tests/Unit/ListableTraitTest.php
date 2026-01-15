<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Unit;

use Blamodex\Lists\Models\ListItem;
use Blamodex\Lists\Models\Lists;
use Blamodex\Lists\Tests\Fixtures\DummyListable;
use Blamodex\Lists\Tests\Fixtures\DummyListOwner;
use Blamodex\Lists\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ListableTraitTest extends TestCase
{
    private DummyListOwner $owner;
    private DummyListable $listable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = DummyListOwner::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->listable = DummyListable::create([
            'name' => 'Test Product',
            'description' => 'A test product',
        ]);
    }

    public function testListsRelationshipReturnsMorphToMany(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->listable->lists());
    }

    public function testListsRelationshipReturnsEmptyCollectionByDefault(): void
    {
        $this->assertCount(0, $this->listable->lists);
    }

    public function testListsRelationshipReturnsListsContainingModel(): void
    {
        $list1 = $this->owner->createList(['name' => 'Favorites']);
        $list2 = $this->owner->createList(['name' => 'Wishlist']);

        $list1->addItem($this->listable);
        $list2->addItem($this->listable);

        $this->assertCount(2, $this->listable->fresh()->lists);
    }

    public function testModelCanBeAddedToMultipleLists(): void
    {
        $list1 = $this->owner->createList(['name' => 'Favorites']);
        $list2 = $this->owner->createList(['name' => 'Wishlist']);
        $list3 = $this->owner->createList(['name' => 'Shopping Cart']);

        $list1->addItem($this->listable);
        $list2->addItem($this->listable);
        $list3->addItem($this->listable);

        $this->assertCount(3, $this->listable->fresh()->lists);
        $this->assertTrue($this->listable->isInList($list1));
        $this->assertTrue($this->listable->isInList($list2));
        $this->assertTrue($this->listable->isInList($list3));
    }

    public function testModelCanBeRemovedFromLists(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);

        $list->addItem($this->listable);
        $this->assertTrue($this->listable->isInList($list));

        $list->removeItem($this->listable);
        $this->assertFalse($this->listable->fresh()->isInList($list));
    }

    public function testListItemsMethodReturnsCollection(): void
    {
        $result = $this->listable->listItems();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testListItemsMethodReturnsEmptyCollectionByDefault(): void
    {
        $this->assertCount(0, $this->listable->listItems());
    }

    public function testListItemsMethodReturnsAllListItemsForModel(): void
    {
        $list1 = $this->owner->createList(['name' => 'Favorites']);
        $list2 = $this->owner->createList(['name' => 'Wishlist']);

        $list1->addItem($this->listable);
        $list2->addItem($this->listable);

        $items = $this->listable->listItems();

        $this->assertCount(2, $items);
        $this->assertInstanceOf(ListItem::class, $items->first());
    }

    public function testListItemsMethodReturnsCorrectListItems(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        $items = $this->listable->listItems();

        $this->assertCount(1, $items);
        $this->assertEquals($list->id, $items->first()->list_id);
        $this->assertEquals($this->listable->id, $items->first()->listable_id);
        $this->assertEquals($this->listable->getMorphClass(), $items->first()->listable_type);
    }

    public function testIsInListMethodReturnsTrue(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        $this->assertTrue($this->listable->isInList($list));
    }

    public function testIsInListMethodReturnsFalse(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);

        $this->assertFalse($this->listable->isInList($list));
    }

    public function testIsInListMethodReturnsFalseAfterRemoval(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);

        $list->addItem($this->listable);
        $this->assertTrue($this->listable->isInList($list));

        $list->removeItem($this->listable);
        $this->assertFalse($this->listable->isInList($list));
    }

    public function testIsInListDistinguishesBetweenLists(): void
    {
        $list1 = $this->owner->createList(['name' => 'Favorites']);
        $list2 = $this->owner->createList(['name' => 'Wishlist']);

        $list1->addItem($this->listable);

        $this->assertTrue($this->listable->isInList($list1));
        $this->assertFalse($this->listable->isInList($list2));
    }

    public function testListsRelationshipDoesNotIncludeOtherModelsLists(): void
    {
        $otherListable = DummyListable::create([
            'name' => 'Other Product',
            'description' => 'Another product',
        ]);

        $list1 = $this->owner->createList(['name' => 'Favorites']);
        $list2 = $this->owner->createList(['name' => 'Wishlist']);

        $list1->addItem($this->listable);
        $list2->addItem($otherListable);

        $this->assertCount(1, $this->listable->fresh()->lists);
        $this->assertCount(1, $otherListable->fresh()->lists);
        $this->assertEquals('Favorites', $this->listable->lists->first()->name);
        $this->assertEquals('Wishlist', $otherListable->lists->first()->name);
    }

    public function testMultipleListablesInSameList(): void
    {
        $listable1 = $this->listable;
        $listable2 = DummyListable::create([
            'name' => 'Product 2',
            'description' => 'Second product',
        ]);
        $listable3 = DummyListable::create([
            'name' => 'Product 3',
            'description' => 'Third product',
        ]);

        $list = $this->owner->createList(['name' => 'Favorites']);

        $list->addItem($listable1);
        $list->addItem($listable2);
        $list->addItem($listable3);

        $this->assertCount(3, $list->items);
        $this->assertTrue($listable1->isInList($list));
        $this->assertTrue($listable2->isInList($list));
        $this->assertTrue($listable3->isInList($list));
    }

    public function testListItemsOnlyReturnsItemsForThisModel(): void
    {
        $otherListable = DummyListable::create([
            'name' => 'Other Product',
            'description' => 'Another product',
        ]);

        $list = $this->owner->createList(['name' => 'Favorites']);

        $list->addItem($this->listable);
        $list->addItem($otherListable);

        $thisItems = $this->listable->listItems();
        $otherItems = $otherListable->listItems();

        $this->assertCount(1, $thisItems);
        $this->assertCount(1, $otherItems);
        $this->assertEquals($this->listable->id, $thisItems->first()->listable_id);
        $this->assertEquals($otherListable->id, $otherItems->first()->listable_id);
    }

    public function testListsRelationshipWithTimestamps(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        $pivotItem = $this->listable->lists()->first();

        // The pivot should have timestamps
        $this->assertNotNull($pivotItem->pivot->created_at);
        $this->assertNotNull($pivotItem->pivot->updated_at);
    }

    public function testListsRelationshipReturnsMorphToManyInstance(): void
    {
        $relation = $this->listable->lists();

        $this->assertInstanceOf(MorphToMany::class, $relation);
        $this->assertEquals('listable_type', $relation->getMorphType());
    }

    public function testListsRelationshipReturnsListsModel(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        $lists = $this->listable->lists;

        $this->assertInstanceOf(Lists::class, $lists->first());
    }

    public function testListItemsReturnsListItemModel(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        $items = $this->listable->listItems();

        $this->assertInstanceOf(ListItem::class, $items->first());
    }

    public function testIsInListWithDifferentOwners(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $list1 = $this->owner->createList(['name' => 'My Favorites']);
        $list2 = $otherOwner->createList(['name' => 'Their Favorites']);

        $list1->addItem($this->listable);

        $this->assertTrue($this->listable->isInList($list1));
        $this->assertFalse($this->listable->isInList($list2));
    }

    public function testListsFromDifferentOwnersAreIncluded(): void
    {
        $otherOwner = DummyListOwner::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $list1 = $this->owner->createList(['name' => 'My Favorites']);
        $list2 = $otherOwner->createList(['name' => 'Their Favorites']);

        $list1->addItem($this->listable);
        $list2->addItem($this->listable);

        $lists = $this->listable->fresh()->lists;

        $this->assertCount(2, $lists);
        $listNames = $lists->pluck('name')->toArray();
        $this->assertContains('My Favorites', $listNames);
        $this->assertContains('Their Favorites', $listNames);
    }

    public function testListItemsAfterSoftDelete(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        // Soft delete the list item via the list
        $list->removeItem($this->listable);

        // The listItems method should not return soft deleted items
        $items = $this->listable->listItems();
        $this->assertCount(0, $items);
    }

    public function testListsRelationshipAfterListSoftDelete(): void
    {
        $list = $this->owner->createList(['name' => 'Favorites']);
        $list->addItem($this->listable);

        $this->assertCount(1, $this->listable->fresh()->lists);

        // Soft delete the list
        $list->delete();

        // The lists relationship should not return soft deleted lists
        $this->assertCount(0, $this->listable->fresh()->lists);
    }
}
