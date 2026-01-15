# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-01-15

### Added

#### Core Features
- `Lists` model with polymorphic relationships for list ownership
- `ListItem` model for polymorphic many-to-many relationships
- UUID support for both `Lists` and `ListItem` models
- Automatic slug generation from list names
- Soft deletes support for both models

#### Traits
- `HasLists` trait for models that can own lists (e.g., User, Team)
  - `lists()` morphMany relationship
  - `createList()` method for creating new lists
  - `updateList()` method for updating existing lists
  - `deleteList()` method for soft-deleting lists
  - Authorization checks to ensure owners can only manage their own lists
- `Listable` trait for models that can be added to lists (e.g., Product, Article)
  - `lists()` morphToMany relationship
  - `listItems()` method to get all list items for the model
  - `isInList()` method to check if model is in a specific list

#### Contracts
- `HasListsInterface` for list owner models
- `ListableInterface` for listable models

#### Exceptions
- `ListException` base exception class for all package exceptions
- `ListOwnershipException` for list ownership validation failures
- `InvalidListableException` for unsaved model errors
- `InvalidListAttributeException` for missing or invalid list attributes

#### Service Layer
- `ListService` for programmatic list management
  - `create()` - Create a new list for any model
  - `update()` - Update an existing list
  - `delete()` - Soft delete a list
  - `addItem()` - Add a single item to a list
  - `addItems()` - Add multiple items at once
  - `removeItem()` - Remove a single item from a list
  - `removeItems()` - Remove multiple items at once
  - `hasItem()` - Check if an item is in a list
  - `getItems()` - Get all items in a list
  - `clearItems()` - Remove all items from a list

#### Database
- Migration for `lists` table with:
  - UUID and slug support
  - Polymorphic owner relationship (`lister_id`, `lister_type`)
  - Soft deletes and timestamps
  - Unique constraint on `[lister_id, lister_type, slug]`
- Migration for `list_items` table with:
  - UUID support
  - Foreign key to lists table with cascade delete
  - Polymorphic item relationship (`listable_id`, `listable_type`)
  - Soft deletes and timestamps
  - Unique constraint on `[list_id, listable_id, listable_type]`

#### Configuration
- Configurable table names via `config/lists.php`
- Service provider for automatic registration

#### Testing
- Comprehensive unit tests (167 tests)
- Integration tests (23 tests)
- Exception tests (16 tests for custom domain exceptions)
- Test fixtures for dummy models
- Orchestra Testbench integration

#### Documentation
- Comprehensive README with usage examples
- Contributing guidelines
- MIT License

#### CI/CD
- GitHub Actions workflow for automated testing
- PHP 8.1, 8.2, 8.3 matrix testing
- Laravel 10.x, 11.x matrix testing
- PHP_CodeSniffer (PSR-12) integration
- PHPStan (level 9) integration
- Code coverage reporting with Codecov

[Unreleased]: https://github.com/blamodex/laravel-lists/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/blamodex/laravel-lists/releases/tag/v1.0.0
