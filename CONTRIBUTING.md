# Contributing to Laravel Lists

Thank you for considering contributing to Laravel Lists! Contributions are welcome and will be fully credited.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How to Contribute](#how-to-contribute)
- [Development Setup](#development-setup)
- [Code Style](#code-style)
- [Testing Requirements](#testing-requirements)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/version/2/1/code_of_conduct/). By participating, you are expected to uphold this code.

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- A clear, descriptive title
- Steps to reproduce the issue
- Expected vs. actual behavior
- PHP and Laravel versions
- Any relevant code snippets or error messages

### Suggesting Features

Feature requests are welcome! Please provide:

- A clear description of the feature
- The problem it solves
- Example use cases
- Any implementation ideas you have

### Submitting Changes

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests and code quality checks
5. Commit your changes using conventional commits
6. Push to your fork
7. Open a Pull Request

## Development Setup

### Requirements

- PHP 8.1 or higher
- Composer 2.x
- Git

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/laravel-lists.git
cd laravel-lists

# Install dependencies
composer install

# Run tests to verify setup
composer test
```

### Available Commands

| Command | Description |
|---------|-------------|
| `composer test` | Run PHPUnit tests |
| `composer test:coverage` | Run tests with code coverage report |
| `composer lint` | Check code style with PHP_CodeSniffer |
| `composer lint:fix` | Fix code style issues automatically |
| `composer analyze` | Run PHPStan static analysis |

## Code Style

This project follows the [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/).

### Requirements

1. **Strict Types**: All PHP files must declare strict types:
   ```php
   <?php

   declare(strict_types=1);
   ```

2. **Type Declarations**: Use type hints for all:
   - Method parameters
   - Return types
   - Property types (PHP 7.4+)

3. **Naming Conventions**:
   - Classes: `PascalCase`
   - Methods and variables: `camelCase`
   - Constants: `SCREAMING_SNAKE_CASE`
   - Configuration keys: `snake_case`

4. **Documentation**:
   - All public methods must have PHPDoc blocks
   - Include `@param`, `@return`, and `@throws` annotations
   - Document any non-obvious behavior

### Checking Code Style

```bash
# Check for style issues
composer lint

# Automatically fix issues
composer lint:fix
```

### Static Analysis

This project uses PHPStan at level 9 (maximum strictness):

```bash
composer analyze
```

All code must pass PHPStan with zero errors.

## Testing Requirements

**All contributions must include tests.** Your pull request will not be accepted without adequate test coverage.

### Test Structure

```
tests/
├── Unit/           # Unit tests for individual classes
├── Integration/    # Integration tests for combined functionality
├── Fixtures/       # Test doubles and dummy models
│   └── migrations/ # Test database migrations
└── TestCase.php    # Base test case
```

### Writing Tests

1. **Unit Tests**: Test individual methods in isolation
   ```php
   public function test_list_can_be_created_with_name(): void
   {
       $owner = DummyListOwner::create(['name' => 'Test', 'email' => 'test@example.com']);
       $list = $owner->createList(['name' => 'My List']);

       $this->assertEquals('My List', $list->name);
   }
   ```

2. **Integration Tests**: Test features working together
   ```php
   public function test_full_workflow(): void
   {
       // Create owner and list
       // Add items
       // Verify relationships
       // Clean up
   }
   ```

3. **Test Naming**: Use descriptive names with snake_case
   - `test_it_creates_list_with_valid_attributes`
   - `test_it_throws_exception_when_name_missing`

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
vendor/bin/phpunit tests/Unit/ListsTest.php

# Run specific test method
vendor/bin/phpunit --filter test_list_can_be_created
```

### Coverage Requirements

While 100% coverage is not always practical, strive for:

- All public methods tested
- All branches (if/else) covered
- All exception paths tested
- Edge cases handled

## Pull Request Process

### Before Submitting

1. **Run all checks**:
   ```bash
   composer test
   composer lint
   composer analyze
   ```

2. **Update documentation** if you changed behavior

3. **Add changelog entry** for significant changes

### PR Guidelines

- **One feature per PR**: If you want to do more than one thing, send multiple pull requests
- **Clear title**: Use conventional commit format (e.g., `feat: add bulk delete method`)
- **Description**: Explain what changes you made and why
- **Tests**: Include tests for new functionality
- **Clean history**: Squash intermediate commits

### Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types**:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `style`: Code style (formatting, no code change)
- `refactor`: Code change that neither fixes a bug nor adds a feature
- `perf`: Performance improvement
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples**:
```
feat(lists): add bulk delete method
fix(service): handle empty listables array
docs: update installation instructions
test: add coverage for edge cases
```

### Review Process

1. Automated checks must pass (tests, linting, static analysis)
2. At least one maintainer review required
3. Address any feedback
4. Maintainer will merge when approved

## Release Cycle

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backwards compatible)
- **PATCH**: Bug fixes (backwards compatible)

## Questions?

If you have questions about contributing, feel free to:

- Open a [GitHub Discussion](https://github.com/blamodex/laravel-lists/discussions)
- Open an issue with the `question` label

**Happy coding!**
