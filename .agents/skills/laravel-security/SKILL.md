---
name: laravel-security
description: >-
  Expert guide and actionable workflows for cybersecurity, vulnerability auditing, and hardening in Laravel applications.
  Use when conducting security audits, fixing vulnerabilities (IDOR, broken access control, SQL injection, XSS, PII data leaks),
  implementing authentication/authorization (RBAC, Gates, Policies, Department Scoping), anti-abuse mechanisms
  (brute-force login defense, anti-DDoS rate limiting, IP blacklists), and writing automated security tests.
---

# Laravel Cybersecurity & Application Hardening Skill

This skill provides comprehensive cybersecurity workflows, checklists, and implementation patterns tailored for Laravel applications.

---

## 1. Security Audit & Vulnerability Assessment Checklist

When auditing a Laravel application, systematically check for these critical vectors:

### A. Access Control & Authorization (Broken Object Level Authorization / RBAC)
- [ ] **Screen & Controller Guards**: Ensure all administrative routes/screens have explicit permissions defined. In Orchid, verify `permission(): ?iterable` is present on every Screen.
- [ ] **IDOR (Insecure Direct Object Reference)**: Never trust user-supplied IDs in request parameters, cookies, or payloads (e.g. `user_id`, `specialty_id`, `account_id`).
  - Always verify ownership: `$entity->user_id === Auth::id()` or check against allowed collections.
  - If a user can switch profiles/specialties, ensure the requested target belongs to the authenticated user.
- [ ] **Department / Tenant Scoping**:
  - Staff users (e.g. Dekanat) must be restricted to their assigned department (`department_id`).
  - Scope queries automatically via Eloquent Global Scopes or explicit policy checks before viewing/editing/exporting records.

### B. Personal Identifiable Information (PII) Protection & GDPR
- [ ] **Model Masking (`$hidden`)**: Ensure sensitive personal fields (tax IDs / RNOKPP, passport series/numbers, birth dates, citizenship) are declared in the Eloquent model's `$hidden` property so they are never leaked in API JSON responses or arrays.
- [ ] **Database Encryption**: Encrypt sensitive data at rest using Laravel's `'encrypted'` model cast for fields like government IDs or personal tokens.
- [ ] **API Endpoint Protection**: Never expose open `/api/*` endpoints returning student/user lists without `auth` middleware and role checks.

### C. Authentication & Anti-Abuse (Brute Force, DoS, DDoS)
- [ ] **Login Brute-Force Defense**:
  - Tier 1: Throttle failed login attempts per IP and username (e.g., 5 failed attempts in 1 min -> 60s cooldown).
  - Tier 2: Escalate repeated failures (e.g., 10 consecutive failures) to automatic temporary IP blacklist (24 hours).
  - Clear rate limiters and failure counters on successful login.
- [ ] **Anti-DDoS Request Rate Limiting**:
  - Track requests in cache using 60-second sliding windows.
  - Set threshold (e.g., 180 requests/min per IP) to catch automated scanners and denial-of-service floods.
  - Automatically blacklist offending IPs and return `403 Forbidden`.
  - Maintain an `ip_whitelist` for localhost (`127.0.0.1`, `::1`) and trusted proxies.
- [ ] **Heavy Endpoint Throttling**:
  - Excel/PDF report generation and CSV exports must be rate-limited (e.g., `throttle:20,1`) to prevent server memory exhaustion.

### D. Session & Cookie Hardening
- [ ] Ensure `.env.example` and production `.env` configure:
  - `SESSION_SECURE_COOKIE=true` (forces HTTPS transmission of session cookies).
  - `SESSION_HTTP_ONLY=true` (prevents JavaScript access to session cookie, mitigating XSS session hijacking).
  - `SESSION_SAME_SITE=lax` (or `strict`, prevents CSRF).
  - `APP_DEBUG=false` in all staging and production environments to prevent leaking stack traces, environment secrets, and database credentials.

---

## 2. Implementation Patterns

### Pattern 1: Department Scoping
```php
public function checkDepartmentAccess(Group $group): void
{
    $user = Auth::user();

    // Administrators have university-wide access
    if ($user->hasAccess('platform.systems.roles') || $user->roles->contains('slug', 'administrator')) {
        return;
    }

    // Dekanat is restricted to their own department
    if ($user->hasAccess('dekanat') || $user->roles->contains('slug', 'dekanat')) {
        if ($user->department_id && $group->department_id !== $user->department_id) {
            abort(403, 'Доступ заборонено: група належить іншому підрозділу.');
        }
    }
}
```

### Pattern 2: Cache-First IP Blacklisting
```php
public static function isBlocked(string $ip): bool
{
    $cacheKey = 'ip_blocked:' . $ip;

    return (bool) Cache::remember($cacheKey, 60, function () use ($ip) {
        $record = static::where('ip_address', $ip)->first();
        if (! $record) {
            return false;
        }
        if ($record->blocked_until !== null && $record->blocked_until->isPast()) {
            $record->delete();
            return false;
        }
        return true;
    });
}
```

### Pattern 3: Automated Security Testing
Always verify security constraints with automated PHPUnit Feature tests:
```php
public function test_unauthorized_user_cannot_access_protected_endpoint(): void
{
    $student = User::factory()->create(['permissions' => ['platform.index' => true]]);
    
    $response = $this->actingAs($student)->get('/admin/security/ip-blacklist');
    $response->assertStatus(403);
}

public function test_idor_cookie_tampering_is_prevented(): void
{
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $cardB = UserSpecialty::factory()->create(['user_id' => $userB->id]);

    $response = $this->actingAs($userA)
        ->withCookie('user_specialty_id', (string) $cardB->id)
        ->get('/admin');

    // Asserts userA cannot see userB's data
    $response->assertDontSee($cardB->full_name);
}
```
