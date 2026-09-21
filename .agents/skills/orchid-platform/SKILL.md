---
name: orchid-platform
description: >-
  Comprehensive guide and patterns for developing administrative dashboards with Orchid Platform in Laravel.
  Use when creating or updating Orchid Screens (query, commandBar, layout), designing Table/Rows/Modal layouts,
  configuring RBAC permissions and menus in PlatformProvider, and solving Orchid-specific gotchas (nested HTML forms,
  Turbo navigation, unclosed post-form state, or abandonment warning dialogs).
---

# Orchid Platform Development Skill

This skill provides essential guidelines, architectural patterns, and troubleshooting runbooks for building admin panels with [Orchid Platform](https://orchid.software) in Laravel.

---

## 1. Orchid Screen Anatomy

Every Orchid Screen in `app/Orchid/Screens/` extends `Orchid\Screen\Screen` and implements four key methods:

```php
namespace App\Orchid\Screens\Example;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExampleListScreen extends Screen
{
    // 1. Screen Title & Subtitle
    public function name(): ?string
    {
        return 'Назва екрана';
    }

    public function description(): ?string
    {
        return 'Короткий опис призначення екрана';
    }

    // 2. Permission Guard (Mandatory for RBAC)
    public function permission(): ?iterable
    {
        return [
            'platform.systems.roles',
        ];
    }

    // 3. Query Dataset
    public function query(): array
    {
        return [
            'items' => Item::latest()->paginate(20),
        ];
    }

    // 4. Command Bar Action Buttons
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Додати')
                ->modal('createModal')
                ->method('save')
                ->icon('bs.plus-circle'),
        ];
    }

    // 5. Layout Definitions
    public function layout(): array
    {
        return [
            Layout::table('items', [
                TD::make('id', 'ID')->width('80px'),
                TD::make('name', 'Назва')->sort(),
                TD::make('created_at', 'Дата створення')
                    ->render(fn (Item $item) => $item->created_at?->format('d.m.Y H:i')),
            ]),
        ];
    }
}
```

---

## 2. Common Layout Types

### A. Tables (`Layout::table`)
```php
Layout::table('collection_key', [
    TD::make('id', 'ID'),
    TD::make('title', 'Заголовок')
        ->render(fn ($row) => "<span class='fw-bold'>{$row->title}</span>"),
    TD::make('actions', 'Дії')
        ->render(fn ($row) => Button::make('Видалити')
            ->icon('bs.trash')
            ->confirm('Ви впевнені?')
            ->method('delete', ['id' => $row->id])
        ),
])
```

### B. Modals (`Layout::modal`)
```php
Layout::modal('editModal', [
    Layout::rows([
        Input::make('item.title')
            ->title('Назва')
            ->required(),
        Select::make('item.status')
            ->options(['active' => 'Активний', 'archived' => 'Архівний']),
    ]),
])->title('Редагування запису')->applyButton('Зберегти')
```

### C. Custom Blade Views (`Layout::view`)
For custom dashboards, analytics, or complex HTML widgets:
```php
Layout::view('analytics.dashboard_content')
```

---

## 3. Orchid Gotchas & Troubleshooting

### A. Avoid Nested `<form>` Tags in Custom Blade Layouts
- **Problem**: Orchid wraps the entire Screen in `<form id="post-form">`. Standard HTML5 forbids nesting `<form>` inside `<form>`, which breaks CSRF token submission and causes buttons to get stuck in `.btn-loading`.
- **Solution**: In custom Blade templates inside Orchid, use `<div>` with button action handlers or disassociate input elements via `form="dummyFormId"`.

### B. Preventing "Leave site? Changes you made may not be saved" Dialog
- **Problem**: Orchid listens to `change` events inside `#post-form` and sets `hasBeenChangedValue = true`. On navigation, it prompts the user with an abandonment warning.
- **Solution**:
  1. In the Screen, disable abandonment tracking:
     ```php
     public function needPreventsAbandonment(): bool
     {
         return false;
     }
     ```
  2. For search/filter inputs in custom layouts, stop event propagation:
     ```html
     <input type="text" form="dummyFilterForm" oninput="event.stopPropagation()" onchange="event.stopPropagation()">
     ```

### C. Large File / Excel Exports in Orchid
- Never trigger heavy binary exports through AJAX/POST inside an Orchid form (risk of CSRF token expiration / HTTP 419).
- Use a direct GET route with `Link::make`:
  ```php
  Link::make('Експорт в Excel (.xlsx)')
      ->icon('bs.file-earmark-spreadsheet')
      ->href(route('export.analytics.excel'))
  ```

---

## 4. Registering Menu & Permissions in `PlatformProvider`

Add navigation links in `app/Orchid/PlatformProvider.php`:
```php
public function menu(): array
{
    return [
        Menu::make('Чорний список IP')
            ->icon('bs.shield-slash')
            ->permission('platform.systems.roles')
            ->route('platform.systems.ip-blacklist'),
    ];
}
```
