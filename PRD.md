# Blamodex Laravel Lists

A lightweight Laravel package to manage lists with polymorphic relationships, suitable for attaching any Eloquent model to user-defined lists.

## 📋 Table of Contents

- Features
- Installation
- Configuration
- Usage
- Database Schema
- Testing
- Project Structure
- Contributing
- License

## 🚀 Features

- Polymorphic `List` model that can be owned by any Eloquent model via a trait
- Polymorphic `ListItem` pivot model to attach any model to lists
- UUID generation and slug support for lists
- Service layer (`ListService`) for create/update/delete/list operations
- Soft deletes and comprehensive test coverage via Orchestra Testbench
- Type-safe contracts and interfaces

## 📦 Installation

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

## ⚙️ Configuration

Configuration lives in `config/lists.php`:

```php
return [
    // Default list table name
    'table_names' => [
        'lists' => 'lists',
        'list_items' => 'list_items',
    ],
];
```

## 🧩 Usage

### 1. Use the `Lister` trait on models

For models that can own lists (e.g., User, Team):

```php
use Blamodex\Lists\Traits\Lister;
use Blamodex\Lists\Contracts\ListerInterface;

class User extends Model implements ListerInterface
{
    use Lister;
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

## 📊 Database Schema

### `lists` table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Unique identifier |
| slug | string | URL-friendly identifier |
| name | string | List name |
| lister_id | bigint | Polymorphic owner ID |
| lister_type | string | Polymorphic owner type |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |

### `list_items` table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Unique identifier |
| list_id | bigint | Foreign key to lists |
| listable_id | bigint | Polymorphic item ID |
| listable_type | string | Polymorphic item type |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |

## 🧪 Testing

This package uses [Orchestra Testbench](https://github.com/orchestral/testbench) and [PHPUnit](https://phpunit.de/).

Run tests:

```bash
composer test
```

Run tests with code coverage:

```bash
composer test:coverage
```

Check code style:

```bash
composer lint
```

Check code style and fix:

```bash
composer lint:fix
```

## 📁 Project Structure

```
src/
├── Models/
│   ├── Lists.php
│   └── ListItem.php
├── Services/
│   └── ListService.php
├── Traits/
│   ├── Lister.php
│   └── Listable.php
├── Contracts/
│   ├── ListerInterface.php
│   └── ListableInterface.php
├── config/
│   └── lists.php
└── database/
    └── migrations/
        ├── create_lists_table.php
        └── create_list_items_table.php

tests/
├── Unit/
│   ├── ListServiceTest.php
│   ├── ListTest.php
│   ├── ListItemTest.php
│   └── TraitsTest.php
├── Fixtures/
│   ├── DummyListOwner.php
│   └── DummyListable.php
└── TestCase.php
```

## 📄 License

MIT © [Blamodex](https://github.com/blackmage-codex)

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 🔗 Links

- [Report a Bug](https://github.com/blamodex/laravel-lists/issues)
- [Request a Feature](https://github.com/blamodex/laravel-lists/issues)
- [View Changelog](CHANGELOG.md)

---

## Implementation Notes

### Key Differences from laravel-addresses:

1. **Dual polymorphic relationships**: Both the list owner (lister) and list items (listable) use polymorphic relationships
2. **Many-to-many through list_items**: Items can belong to multiple lists, and lists can have multiple items
3. **Slug generation**: Lists have slugs for URL-friendly identifiers
4. **Two traits**: `HasLists` for owners and `Listable` for items that can be listed

### Development Priorities:

1. ✅ Database migrations (lists, list_items)
2. ✅ Models with relationships (List, ListItem)
3. ✅ Traits (HasLists, Listable)
4. ✅ Contracts/Interfaces
5. ✅ Service layer (ListService)
6. ✅ Unit tests with Orchestra Testbench
7. ✅ Configuration file
8. ✅ Documentation (README)
9. ✅ CI/CD (GitHub Actions)
