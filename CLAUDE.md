# ModelFlow AI Development Guide

## Build/Test Commands
- `composer test` - Run all tests
- `cd packages/PACKAGE && composer test` - Run tests for specific package
- `cd packages/PACKAGE && vendor/bin/phpunit tests/Unit/Path/TestFile.php` - Run single test
- `composer fix` - Apply code style fixes (Rector + PHP-CS-Fixer)
- `composer lint` - Run static analysis (PHPStan + PHP-CS-Fixer + Rector)
- `composer test-with-coverage` - Generate test coverage reports

## Code Style Guidelines
- PHP 8.2+, strict typing with `declare(strict_types=1);`
- PSR-4 namespace structure following directory layout
- Strong type hinting (parameters and return types)
- Constructor property promotion when applicable
- Use interfaces for better abstraction and testability
- Follow Symfony code style standards
- Short array syntax `[]` instead of `array()`
- Always use strict comparison (`===`, `!==`)
- Use native PHP functions with `\` prefix in the global namespace

## Documentation Philosophy
- **Self-documenting code over comments** - Clear naming + type hints > redundant docstrings
- **Only add docstrings when they add value** - Complex algorithms, non-obvious behavior, or important context
- **Type hints are documentation** - Modern PHP 8.2+ with strict types makes @param/@return tags redundant
- **Tests as documentation** - Well-written tests show how code should be used
- **No docstring coverage requirements** - Configured in `.coderabbit.yaml` to avoid noise

## Testing Standards
- Unit tests for all classes
- PHPUnit 10.3+ with strict assertions
- Use ProphecyTrait for mocking in tests
- Test naming: `testMethodName`, `testMethodNameWithCondition`
- Exception testing: `@expectException` annotation

## Git Commit Guidelines
- **NO AI attribution in commit messages** - Never include Claude name, co-author, or "Generated with Claude Code"
- **Clear, descriptive messages** - Explain what changed and why
- **Professional tone** - Keep messages clean and focused on the change

## Error Handling
- Use exception hierarchies with specific exception classes
- Prefer early returns over deep nesting
- Follow the principle of fail-fast
- Always validate input parameters with Webmozart Assert or native functions