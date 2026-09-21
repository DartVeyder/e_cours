---
name: php-code-quality
description: >-
  Expert guidelines and best practices for PHP 8.2+ code quality, strict typing, null-safety,
  static analysis, refactoring, and clean code principles. Use when writing, reviewing, or refactoring
  PHP code to ensure strict type safety, eliminate deprecation warnings, handle exceptions gracefully,
  and adhere to PSR-12 and Laravel Pint standards.
---

# PHP 8.2+ Code Quality & Modern Standards Skill

This skill provides reference patterns and guidelines for writing robust, performant, and type-safe PHP 8.2+ code.

---

## 1. Strict Typing & Type Safety

### A. Strict Types Declaration
Always declare strict types at the very top of new classes, controllers, and services:
```php
<?php

declare(strict_types=1);

namespace App\Services;
```

### B. Typed Properties & Constructor Promotion
Leverage PHP 8 constructor property promotion and explicit types:
```php
class AnalyticsService
{
    public function __construct(
        protected readonly StudentsSheet $studentsSheet,
        protected readonly int $cohortYear = 2026,
    ) {
    }
}
```

### C. Return Types & Union Types
Always specify explicit return types on methods and functions:
```php
public function findActiveCard(int $userId): ?UserSpecialty
{
    return UserSpecialty::where('user_id', $userId)->first();
}

public function formatDuration(int|float $seconds): string
{
    return sprintf('%.2f сек.', $seconds);
}
```

---

## 2. Null Safety & Error Handling

### A. Null-Safe Operator (`?->`) and Null Coalescing (`??`)
Avoid manual `isset()` or `is_null()` ladders:
```php
// Good: Safe navigation and fallback
$departmentName = $group?->department?->name ?? 'Загальноуніверситетський';

// Good: Null coalescing assignment
$this->cache[$key] ??= $this->computeExpensiveValue($key);
```

### B. Defensive Date Parsing
When receiving variable date formats (e.g. from Google Sheets or user inputs), normalize gracefully without fatal exceptions:
```php
public static function parseFlexibleDate(?string $value): ?Carbon
{
    if (empty($value) || $value === '?') {
        return null;
    }

    $formats = ['Y-m-d', 'd.m.Y', 'm/d/Y', 'd/m/Y'];
    foreach ($formats as $format) {
        try {
            return Carbon::createFromFormat($format, trim($value))->startOfDay();
        } catch (\Exception) {
            continue;
        }
    }

    return null;
}
```

---

## 3. Code Style & PSR-12 Conventions

### A. Formatting Guidelines (Laravel Pint)
- **Indentation**: 4 spaces, no tabs.
- **Braces**: Opening brace on a new line for classes and functions; on the same line for control structures (`if`, `for`, `foreach`).
- **Imports**: Grouped alphabetically, unused imports removed.
- **Naming Conventions**:
  - Classes, Interfaces, Traits: `StudlyCase`
  - Methods, variables, properties: `camelCase`
  - Constants, configuration keys, database columns: `snake_case`

### B. Safe Collection Pipelines
Use higher-order collection methods for clean data transformation:
```php
$activeEmails = $users
    ->filter->isActive()
    ->map->email
    ->filter()
    ->unique()
    ->values()
    ->all();
```
