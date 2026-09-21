---
name: laravel-development
description: >-
  Comprehensive guide and best practices for modern Laravel 11 development, application architecture,
  Eloquent ORM query optimization, service layer patterns, migrations, and automated testing.
  Use when designing new modules, writing database migrations, optimizing database queries (N+1 prevention),
  refactoring business logic into dedicated services/actions, and writing robust PHPUnit tests.
---

# Laravel 11 Application Development Skill

This skill outlines core architectural principles, standard conventions, and implementation workflows for modern Laravel 11 applications.

---

## 1. Application Architecture & Conventions

### A. Lean Controllers & Screens (Service Layer Pattern)
- Keep controllers and Orchid screens focused on HTTP concerns (request validation, triggering actions, returning views/responses).
- Move complex business logic, analytics calculations, or third-party API integrations into dedicated service classes in `app/Services/`:
  - `app/Services/Analytics/AnalyticsService.php`
  - `app/Services/GoogleSheet/StudentsSheet.php`
  - `app/Services/Export/ExcelExportService.php`

### B. Middleware Registration in Laravel 11
In Laravel 11, middleware is declared in `bootstrap/app.php` via `withMiddleware`:
```php
->withMiddleware(function (Middleware $middleware): void {
    // Custom redirect for unauthenticated requests
    $middleware->redirectGuestsTo('/login');

    // Prepend to run before everything (e.g. IP blocking)
    $middleware->prepend(CheckIpBlacklist::class);

    // Append to global stack (e.g. rate limiters)
    $middleware->append(DetectDdosAbuse::class);

    // Group modifications (web, api)
    $middleware->web(append: [
        ProtectLoginBruteForce::class,
    ]);
})
```

---

## 2. Eloquent ORM Best Practices & Performance

### A. Modern Casts Method (Laravel 11)
Prefer the `casts()` method over the legacy `$casts` property:
```php
protected function casts(): array
{
    return [
        'blocked_until' => 'datetime',
        'is_active' => 'boolean',
        'options' => 'array',
        'request_count' => 'integer',
    ];
}
```

### B. Preventing the N+1 Query Problem
- Always eager-load relationships when fetching collections for tables or exports:
  ```php
  // Good: Eager loading
  $students = UserSpecialty::with(['user', 'group.department', 'subjects'])->paginate(25);
  ```
- Use `loadMissing()` if an existing model instance might not have the relation loaded:
  ```php
  $user->loadMissing('roles');
  ```

### C. Large Datasets & Memory Optimization
- When exporting large amounts of data to Excel or CSV, avoid loading all records into memory at once (`Model::all()`).
- Use `chunk()`, `cursor()`, or `lazy()` for streaming large recordsets:
  ```php
  UserSpecialty::query()
      ->with('group')
      ->lazy(500)
      ->each(function (UserSpecialty $student) use ($writer) {
          $writer->addRow($student->toExportArray());
      });
  ```

---

## 3. Database Migrations & Schemas

### A. Safe Migration Conventions
- Always declare strict types, sensible defaults, and proper indexing on frequently queried columns:
  ```php
  Schema::create('ip_blacklists', function (Blueprint $table) {
      $table->id();
      $table->string('ip_address', 45)->unique()->index();
      $table->string('reason');
      $table->unsignedInteger('request_count')->default(0);
      $table->timestamp('blocked_until')->nullable()->index();
      $table->timestamps();
  });
  ```
- Ensure SQLite and MySQL cross-compatibility in tests and production.

---

## 4. Automated Testing (Feature & Unit)

### A. Test Case Structure
- Use `use RefreshDatabase;` for database tests.
- Mock external APIs (Google Sheets, social OAuth) using Laravel's built-in fakes (`Http::fake()`, `Event::fake()`, `Queue::fake()`).
- Always verify both positive (authorized, valid input) and negative (unauthorized, invalid input, rate limits) scenarios.

### B. Test Execution
```bash
# Run entire test suite
php artisan test

# Run a specific test class
php artisan test --filter=SecurityHardeningTest

# Stop on first failure
php artisan test --stop-on-failure
```
