# Blamodex Laravel Lists

[![Tests](https://github.com/blamodex/laravel-lists/actions/workflows/tests.yml/badge.svg)](https://github.com/blamodex/laravel-lists/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/blamodex/laravel-lists.svg)](https://packagist.org/packages/blamodex/laravel-lists)
[![License](https://img.shields.io/packagist/l/blamodex/laravel-lists.svg)](https://packagist.org/packages/blamodex/laravel-lists)

A lightweight Laravel package to manage lists with polymorphic relationships, suitable for attaching any Eloquent model to user-defined lists.

## Features

- Polymorphic `List` model that can be owned by any Eloquent model via a trait
- Polymorphic `ListItem` pivot model to attach any model to lists
- UUID generation and slug support for lists
- Service layer (`ListService`) for create/update/delete/list operations
- Soft deletes and comprehensive test coverage via Orchestra Testbench
- Type-safe contracts and interfaces

## Installation

Install the package with Composer:

```bash
composer require blamodex/laravel-lists
```

Publish the config file:

```bash
php artisan vendor:publish --tag=blamodex-lists-config
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

Configuration lives in `config/lists.php`:

```php
return [
    'table_names' => [
        'lists' => 'lists',
        'list_items' => 'list_items',
    ],
];
```

## Usage

### 1. Use the `HasLists` trait on models

For models that can own lists (e.g., User, Team):

```php
use Blamodex\Lists\Traits\HasLists;
use Blamodex\Lists\Contracts\HasListsInterface;

class User extends Model implements HasListsInterface
{
    use HasLists;
}
```

### 2. Use the `Listable` trait on models

For models that can be added to lists:

```php
use Blamodex\Lists\Traits\Listable;
use Blamodex\Lists\Contracts\ListableInterface;

class Product extends Model implements ListableInterface
{
    use Listable;
}
```

### 3. Create a list

```php
$user = User::find(1);

// Using the trait method
$list = $user->createList([
    'name' => 'My Favorite Products',
    'slug' => 'favorite-products', // optional, auto-generated if not provided
]);

// Or using the service directly
$service = app(\Blamodex\Lists\Services\ListService::class);
$list = $service->create($user, [
    'name' => 'My Favorite Products',
]);
```

### 4. Add items to a list

```php
$product = Product::find(1);

// Add a single item
$list->addItem($product);

// Add multiple items
$list->addItems([$product1, $product2, $product3]);

// Or via service
$service->addItem($list, $product);
```

### 5. Remove items from a list

```php
$list->removeItem($product);

// Remove multiple items
$list->removeItems([$product1, $product2]);

// Or via service
$service->removeItem($list, $product);
```

### 6. Check if an item is in a list

```php
if ($list->hasItem($product)) {
    // Product is in the list
}
```

### 7. Get all items in a list

```php
$items = $list->items; // Returns collection of list_items
$products = $list->items()->with('listable')->get()->pluck('listable');
```

### 8. Get all lists for a model

```php
// Get all lists owned by a user
$lists = $user->lists;

// Get all lists containing a product
$lists = $product->lists;
```

### 9. Update a list

```php
$user->updateList($list, ['name' => 'My Best Products']);

// Or via service
$service->update($list, ['name' => 'My Best Products']);
```

### 10. Delete a list

```php
$user->deleteList($list);

// Or via service
$service->delete($list);
```

## Testing

Run tests:

```bash
composer test
```

Run tests with code coverage:

```bash
composer test:coverage
```

## License

MIT © [Blamodex](https://github.com/blamodex)

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.
