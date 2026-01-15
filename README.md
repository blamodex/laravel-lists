# Blamodex Laravel Lists

[![Tests](https://github.com/blamodex/laravel-lists/actions/workflows/tests.yml/badge.svg)](https://github.com/blamodex/laravel-lists/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/blamodex/laravel-lists.svg)](https://packagist.org/packages/blamodex/laravel-lists)
[![License](https://img.shields.io/packagist/l/blamodex/laravel-lists.svg)](https://packagist.org/packages/blamodex/laravel-lists)
[![PHP Version](https://img.shields.io/packagist/php-v/blamodex/laravel-lists.svg)](https://packagist.org/packages/blamodex/laravel-lists)

A lightweight Laravel package to manage lists with polymorphic relationships, suitable for attaching any Eloquent model to user-defined lists.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Features

- **Polymorphic List Ownership**: Any Eloquent model can own lists via the `HasLists` trait (e.g., User, Team, Organization)
- **Polymorphic List Items**: Any Eloquent model can be added to lists via the `Listable` trait (e.g., Product, Article, Post)
- **UUID Support**: All lists and list items have UUID identifiers for external references
- **Slug Generation**: Automatic slug generation from list names for URL-friendly identifiers
- **Service Layer**: `ListService` provides a clean API for create/update/delete/manage operations
- **Soft Deletes**: Lists and list items support soft deletes for data recovery
- **Type-Safe Contracts**: Interfaces for both list owners (`HasListsInterface`) and listable models (`ListableInterface`)
- **Configurable Table Names**: Customize database table names via configuration
- **Comprehensive Test Coverage**: Full test suite with Orchestra Testbench

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Installation

Install the package via Composer:

```bash
composer require blamodex/laravel-lists
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=blamodex-lists-config
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file is located at `config/lists.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | You can customize the table names used by this package.
    |
    */
    'table_names' => [
        'lists' => 'lists',
        'list_items' => 'list_items',
    ],
];
```

## Usage

### Setting Up Models

#### List Owners (e.g., User, Team)

Add the `HasLists` trait and implement `HasListsInterface` on models that can create and own lists:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Blamodex\Lists\Traits\HasLists;
use Blamodex\Lists\Contracts\HasListsInterface;

class User extends Authenticatable implements HasListsInterface
{
    use HasLists;

    // ...
}
```

#### Listable Models (e.g., Product, Article)

Add the `Listable` trait and implement `ListableInterface` on models that can be added to lists:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Blamodex\Lists\Traits\Listable;
use Blamodex\Lists\Contracts\ListableInterface;

class Product extends Model implements ListableInterface
{
    use Listable;

    // ...
}
```

### Creating Lists

```php
$user = User::find(1);

// Using the trait method
$list = $user->createList([
    'name' => 'My Favorite Products',
    'slug' => 'favorite-products', // Optional - auto-generated from name if not provided
]);

// Using the service directly
$service = app(\Blamodex\Lists\Services\ListService::class);
$list = $service->create($user, [
    'name' => 'My Favorite Products',
]);
```

### Adding Items to a List

```php
$product = Product::find(1);

// Add a single item
$list->addItem($product);

// Add multiple items at once
$products = Product::whereIn('id', [1, 2, 3])->get();
$list->addItems($products->all());

// Using the service
$service->addItem($list, $product);
$service->addItems($list, $products->all());
```

### Removing Items from a List

```php
// Remove a single item
$list->removeItem($product);

// Remove multiple items
$list->removeItems([$product1, $product2]);

// Using the service
$service->removeItem($list, $product);
$service->removeItems($list, [$product1, $product2]);

// Clear all items from a list
$service->clearItems($list);
```

### Checking List Membership

```php
// Check if an item is in a list
if ($list->hasItem($product)) {
    // Product is in the list
}

// Check from the listable model
if ($product->isInList($list)) {
    // Product is in the list
}

// Using the service
if ($service->hasItem($list, $product)) {
    // Product is in the list
}
```

### Retrieving List Items

```php
// Get all list items (ListItem models)
$items = $list->items;

// Get the actual listable models
$products = $list->items()->with('listable')->get()->pluck('listable');

// Using the service
$items = $service->getItems($list);
```

### Retrieving Lists

```php
// Get all lists owned by a user
$lists = $user->lists;

// Get all lists containing a product
$lists = $product->lists;

// Get all list items for a product
$listItems = $product->listItems();
```

### Updating a List

```php
// Using the trait method (validates ownership)
$user->updateList($list, ['name' => 'My Best Products']);

// Using the service
$service->update($list, ['name' => 'My Best Products']);
```

### Deleting a List

```php
// Using the trait method (validates ownership, soft deletes)
$user->deleteList($list);

// Using the service (soft deletes)
$service->delete($list);
```

## Database Schema

### `lists` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key (auto-increment) |
| `uuid` | uuid | Unique identifier for external references |
| `slug` | string | URL-friendly identifier (indexed) |
| `name` | string | Display name of the list |
| `lister_id` | bigint | Polymorphic owner ID |
| `lister_type` | string | Polymorphic owner type (e.g., `App\Models\User`) |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |
| `deleted_at` | timestamp | Soft delete timestamp (nullable) |

**Indexes:**
- Unique index on `uuid`
- Index on `slug`
- Composite index on `[lister_id, lister_type]`
- Unique constraint on `[lister_id, lister_type, slug]`

### `list_items` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key (auto-increment) |
| `uuid` | uuid | Unique identifier for external references |
| `list_id` | bigint | Foreign key to `lists` table |
| `listable_id` | bigint | Polymorphic item ID |
| `listable_type` | string | Polymorphic item type (e.g., `App\Models\Product`) |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |
| `deleted_at` | timestamp | Soft delete timestamp (nullable) |

**Indexes:**
- Unique index on `uuid`
- Foreign key on `list_id` with cascade delete
- Composite index on `[listable_id, listable_type]`
- Unique constraint on `[list_id, listable_id, listable_type]`

## Testing

This package uses [Orchestra Testbench](https://github.com/orchestral/testbench) for Laravel package testing.

Run the test suite:

```bash
composer test
```

Run tests with code coverage:

```bash
composer test:coverage
```

Run static analysis with PHPStan:

```bash
composer analyze
```

Run code style checks with PHP_CodeSniffer:

```bash
composer lint
```

Automatically fix code style issues:

```bash
composer lint:fix
```

## Project Structure

```
laravel-lists/
├── config/
│   └── lists.php                 # Package configuration
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_lists_table.php
│       └── 2024_01_01_000002_create_list_items_table.php
├── src/
│   ├── Contracts/
│   │   ├── HasListsInterface.php # Interface for list owners
│   │   └── ListableInterface.php # Interface for listable models
│   ├── Models/
│   │   ├── Lists.php             # List model
│   │   └── ListItem.php          # List item pivot model
│   ├── Services/
│   │   └── ListService.php       # Service layer for list operations
│   ├── Traits/
│   │   ├── HasLists.php          # Trait for list owners
│   │   └── Listable.php          # Trait for listable models
│   └── ListsServiceProvider.php  # Package service provider
├── tests/
│   ├── Fixtures/
│   │   ├── DummyListOwner.php    # Test fixture for list owners
│   │   ├── DummyListable.php     # Test fixture for listable models
│   │   └── migrations/           # Test fixture migrations
│   ├── Integration/
│   │   └── ListsIntegrationTest.php
│   ├── Unit/
│   │   ├── HasListsTraitTest.php
│   │   ├── ListableTraitTest.php
│   │   ├── ListItemTest.php
│   │   ├── ListServiceTest.php
│   │   └── ListsTest.php
│   └── TestCase.php              # Base test case
├── .github/
│   └── workflows/
│       └── tests.yml             # GitHub Actions CI/CD
├── composer.json
├── phpunit.xml
├── phpstan.neon
├── .phpcs.xml
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE
└── README.md
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email blackmage.codex@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
