# Project Plan: Laravel Lists Package

## Overview
Building a lightweight Laravel package to manage lists with polymorphic relationships, following the architectural pattern of blamodex/laravel-addresses.

**Reference:** `PRD.md`

---

## Task List

```json
[
  {
    "category": "setup",
    "description": "Initialize package structure and configuration",
    "steps": [
      "Create composer.json with package metadata (blamodex/laravel-lists)",
      "Set up autoloading (PSR-4: Blamodex\\Lists\\)",
      "Create .gitignore, .gitattributes files",
      "Create LICENSE file (MIT)",
      "Create basic README.md, CHANGELOG.md, CONTRIBUTING.md",
      "Set up directory structure: src/, database/, tests/, config/"
    ],
    "passes": true
  },
  {
    "category": "setup",
    "description": "Configure testing infrastructure",
    "steps": [
      "Install Orchestra Testbench as dev dependency",
      "Install PHPUnit as dev dependency",
      "Create phpunit.xml configuration",
      "Create tests/TestCase.php extending Orchestra\\Testbench\\TestCase",
      "Create tests/Fixtures/ directory for dummy models",
      "Configure phpstan.neon for static analysis",
      "Create .phpcs.xml for code style checking"
    ],
    "passes": true
  },
  {
    "category": "setup",
    "description": "Set up GitHub Actions CI/CD",
    "steps": [
      "Create .github/workflows/tests.yml",
      "Configure PHP matrix testing (8.1, 8.2, 8.3)",
      "Add composer install, phpunit, phpstan, phpcs steps",
      "Configure Laravel matrix testing (10.x, 11.x)",
      "Add code coverage reporting"
    ],
    "passes": true
  },
  {
    "category": "database",
    "description": "Create database migrations",
    "steps": [
      "Create database/migrations/create_lists_table.php",
      "Add columns: id, uuid, slug, name, lister_id, lister_type, timestamps, soft deletes",
      "Add indexes on lister_id, lister_type, slug",
      "Create database/migrations/create_list_items_table.php",
      "Add columns: id, uuid, list_id, listable_id, listable_type, timestamps, soft deletes",
      "Add foreign key constraint on list_id",
      "Add indexes on listable_id, listable_type",
      "Add unique constraint on list_id + listable_id + listable_type"
    ],
    "passes": true
  },
  {
    "category": "models",
    "description": "Create List model",
    "steps": [
      "Create src/Models/Lists.php",
      "Add SoftDeletes, HasUuids traits",
      "Define fillable: ['name', 'slug', 'lister_id', 'lister_type']",
      "Add casts: uuid to string",
      "Create lister() morphTo relationship",
      "Create items() hasMany relationship to ListItem",
      "Create listables() hasManyThrough relationship via items",
      "Add boot() method with slug auto-generation from name",
      "Add helper methods: addItem(), addItems(), removeItem(), removeItems(), hasItem()"
    ],
    "passes": true
  },
  {
    "category": "models",
    "description": "Create ListItem model",
    "steps": [
      "Create src/Models/ListItem.php",
      "Add SoftDeletes, HasUuids traits",
      "Define fillable: ['list_id', 'listable_id', 'listable_type']",
      "Add casts: uuid to string",
      "Create list() belongsTo relationship",
      "Create listable() morphTo relationship",
      "Add unique constraint validation in boot() or via database"
    ],
    "passes": true
  },
  {
    "category": "contracts",
    "description": "Create contracts and interfaces",
    "steps": [
      "Create src/Contracts/HasListsInterface.php",
      "Define methods: createList(), updateList(), deleteList(), lists()",
      "Create src/Contracts/ListableInterface.php",
      "Define methods: lists() relationship"
    ],
    "passes": true
  },
  {
    "category": "traits",
    "description": "Create HasLists trait",
    "steps": [
      "Create src/Traits/HasLists.php",
      "Implement lists() morphMany relationship",
      "Implement createList(array $attributes): Lists method",
      "Implement updateList(Lists $list, array $attributes): Lists method",
      "Implement deleteList(Lists $list): bool method",
      "Add authorization checks to ensure lister owns the list"
    ],
    "passes": true
  },
  {
    "category": "traits",
    "description": "Create Listable trait",
    "steps": [
      "Create src/Traits/Listable.php",
      "Implement lists() morphToMany relationship via list_items",
      "Add helper method to check if model is in a specific list",
      "Add helper method to get all list_items for this model"
    ],
    "passes": true
  },
  {
    "category": "services",
    "description": "Create ListService",
    "steps": [
      "Create src/Services/ListService.php",
      "Implement create($lister, array $attributes): Lists method",
      "Implement update(Lists $list, array $attributes): Lists method",
      "Implement delete(Lists $list): bool method with soft delete",
      "Implement addItem(Lists $list, Model $listable): ListItem method",
      "Implement addItems(Lists $list, array $listables): Collection method",
      "Implement removeItem(Lists $list, Model $listable): bool method",
      "Implement removeItems(Lists $list, array $listables): int method",
      "Add validation and error handling"
    ],
    "passes": false
  },
  {
    "category": "config",
    "description": "Create configuration file",
    "steps": [
      "Create config/lists.php",
      "Add table_names configuration for lists and list_items tables",
      "Add any additional configuration options",
      "Document all configuration options"
    ],
    "passes": false
  },
  {
    "category": "service-provider",
    "description": "Create service provider",
    "steps": [
      "Create src/ListsServiceProvider.php extending ServiceProvider",
      "Register configuration in register() method",
      "Publish config file with tag 'blamodex-lists-config'",
      "Load migrations in boot() method",
      "Bind ListService to container if needed"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Create test fixtures",
    "steps": [
      "Create tests/Fixtures/DummyListOwner.php (User-like model)",
      "Add HasLists trait and HasListsInterface",
      "Create tests/Fixtures/DummyListable.php (Product-like model)",
      "Add Listable trait and ListableInterface"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Write unit tests for List model",
    "steps": [
      "Create tests/Unit/ListTest.php",
      "Test list creation with owner",
      "Test slug auto-generation",
      "Test UUID generation",
      "Test relationships (lister, items, listables)",
      "Test soft deletes",
      "Test addItem(), addItems(), removeItem(), removeItems() methods",
      "Test hasItem() method"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Write unit tests for ListItem model",
    "steps": [
      "Create tests/Unit/ListItemTest.php",
      "Test list item creation",
      "Test UUID generation",
      "Test relationships (list, listable)",
      "Test unique constraint (list_id + listable_id + listable_type)",
      "Test soft deletes"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Write unit tests for HasLists trait",
    "steps": [
      "Create tests/Unit/HasListsTraitTest.php",
      "Test lists() relationship",
      "Test createList() method",
      "Test updateList() method",
      "Test deleteList() method",
      "Test authorization (owner can only manage their own lists)"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Write unit tests for Listable trait",
    "steps": [
      "Create tests/Unit/ListableTraitTest.php",
      "Test lists() relationship",
      "Test model can be added to multiple lists",
      "Test model can be removed from lists",
      "Test helper methods"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Write unit tests for ListService",
    "steps": [
      "Create tests/Unit/ListServiceTest.php",
      "Test create() method",
      "Test update() method",
      "Test delete() method (soft delete)",
      "Test addItem() method",
      "Test addItems() method with multiple items",
      "Test removeItem() method",
      "Test removeItems() method",
      "Test validation and error handling",
      "Test edge cases (duplicate items, non-existent items, etc.)"
    ],
    "passes": false
  },
  {
    "category": "testing",
    "description": "Write integration tests",
    "steps": [
      "Create tests/Integration/ListsIntegrationTest.php",
      "Test full workflow: create list, add items, remove items, delete list",
      "Test multiple owners with multiple lists",
      "Test same item in multiple lists",
      "Test polymorphic relationships work correctly",
      "Test migrations run successfully"
    ],
    "passes": false
  },
  {
    "category": "documentation",
    "description": "Complete README.md",
    "steps": [
      "Add package badges (tests, version, license)",
      "Write comprehensive feature list",
      "Document installation steps",
      "Add detailed usage examples for all features",
      "Document database schema with table descriptions",
      "Add testing instructions",
      "Include project structure tree",
      "Add contributing guidelines link",
      "Add license information"
    ],
    "passes": false
  },
  {
    "category": "documentation",
    "description": "Create additional documentation files",
    "steps": [
      "Create CHANGELOG.md with version history",
      "Create CONTRIBUTING.md with contribution guidelines",
      "Add code style requirements",
      "Add pull request template",
      "Document testing requirements for contributions"
    ],
    "passes": false
  },
  {
    "category": "quality",
    "description": "Set up code quality tools",
    "steps": [
      "Configure PHP CS Fixer or PHP_CodeSniffer with PSR-12 standard",
      "Run linter and fix all style issues (must pass with 0 errors/warnings)",
      "Configure PHPStan level 9 (maximum strictness)",
      "Add strict_types declaration to all PHP files",
      "Fix all static analysis issues (must pass with 0 errors)",
      "Configure phpstan-strict-rules for additional checks",
      "Add composer scripts: test, lint, lint:fix, analyze",
      "Add pre-commit hook to run linter and static analysis"
    ],
    "passes": false
  },
  {
    "category": "quality",
    "description": "Achieve 100% test coverage",
    "steps": [
      "Run tests with coverage report (--coverage-html)",
      "Test all public methods in all classes",
      "Test all branches (if/else, switch cases, ternary operators)",
      "Test all exception paths and error handling",
      "Test all trait methods in isolation",
      "Test all model relationships and scopes",
      "Test all service methods with edge cases",
      "Test validation rules (valid and invalid inputs)",
      "Test soft deletes and UUID generation",
      "Test slug generation and uniqueness",
      "Verify 100% line coverage, 100% method coverage, 100% branch coverage",
      "Add @codeCoverageIgnore only where absolutely necessary with justification"
    ],
    "passes": false
  },
  {
    "category": "quality",
    "description": "Strict type safety and error handling",
    "steps": [
      "Add strict type hints to all method parameters",
      "Add strict return type declarations to all methods",
      "Add property type declarations (PHP 7.4+)",
      "Remove all @phpstan-ignore comments",
      "Add custom exceptions for domain-specific errors",
      "Test all exception throwing scenarios",
      "Ensure no @SuppressWarnings or similar annotations",
      "Use array shapes in PHPStan annotations where applicable"
    ],
    "passes": false
  },
  {
    "category": "quality",
    "description": "Code review and quality gates",
    "steps": [
      "Set up GitHub Actions to require 100% coverage to pass",
      "Configure PHPStan level 9 as required check",
      "Configure code style check as required check",
      "Add coverage badge to README showing 100%",
      "Add PHPStan badge showing passing",
      "Add style check badge showing passing",
      "Review all code for best practices and SOLID principles",
      "Ensure no TODO or FIXME comments remain",
      "Verify all docblocks are complete and accurate"
    ],
    "passes": false
  },
  {
    "category": "release",
    "description": "Prepare for initial release",
    "steps": [
      "Verify all tests pass with 100% coverage",
      "Verify PHPStan level 9 passes with 0 errors",
      "Verify code style check passes with 0 issues",
      "Verify CI/CD pipeline passes all quality gates",
      "Review and finalize documentation",
      "Ensure all composer.json metadata is correct",
      "Run 'composer validate --strict'",
      "Tag version 1.0.0",
      "Create GitHub release with detailed release notes",
      "Submit to Packagist if not auto-registered",
      "Verify package can be installed in fresh Laravel project"
    ],
    "passes": false
  }
]
```