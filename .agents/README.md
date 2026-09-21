# Antigravity Workspace Customizations (.agents)

У цій директорії розміщено скілли (Skills) та конфігурації агента Antigravity для проєкту **E-Cours**.

## 🧠 Доступні скілли (Available Skills):

1. **`laravel-security`** ([.agents/skills/laravel-security/SKILL.md](./skills/laravel-security/SKILL.md)):
   - Аудит кібербезпеки та вразливостей (OWASP, IDOR, Broken Access Control, SQLi, XSS).
   - Захист персональних даних (GDPR / ПДН, маскування `$hidden`, шифрування).
   - Захист від брутфорсу (Brute Force), Anti-DDoS лімітування та керування чорним списком IP.
   - Ізоляція підрозділів (Department Scoping) та RBAC у контролерах та екранах.

2. **`laravel-development`** ([.agents/skills/laravel-development/SKILL.md](./skills/laravel-development/SKILL.md)):
   - Архітектура сучасного Laravel 11 (`bootstrap/app.php`, Middlewares).
   - Оптимізація Eloquent запитів (запобігання N+1, `lazy()`, `chunk()`, casts).
   - Сервісний шар (Services/Actions), міграції та фабрики.
   - Автоматизоване тестування з PHPUnit (Feature / Unit tests).

3. **`php-code-quality`** ([.agents/skills/php-code-quality/SKILL.md](./skills/php-code-quality/SKILL.md)):
   - Стандарти PHP 8.2+ та сувора типізація (`declare(strict_types=1);`).
   - Null-safety (`?->`, `??`), захисне парсування неструктурованих дат.
   - Дотримання стандартів коду PSR-12 та Laravel Pint.

4. **`orchid-platform`** ([.agents/skills/orchid-platform/SKILL.md](./skills/orchid-platform/SKILL.md)):
   - Розробка екранів Orchid (`query`, `commandBar`, `layout`, `permission`).
   - Табличні, рядкові, модальні та кастомні Blade-лейаути.
   - Усунення специфічних проблем Orchid (вкладені форми, SPA Turbo-навігація, попередження про незбережені зміни).
