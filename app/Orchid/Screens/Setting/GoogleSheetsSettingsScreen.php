<?php

namespace App\Orchid\Screens\Setting;

use App\Models\Setting;
use App\Services\GoogleSheet\GoogleSheetService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GoogleSheetsSettingsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'google_sheets_test_mode' => GoogleSheetService::isTestMode(),

            // Таблиця «Студенти» (Production & Test)
            'google_sheets_production_students_id'  => Setting::where('key', 'google_sheets_production_students_id')->value('value') 
                ?? GoogleSheetService::DEFAULT_STUDENTS_SHEET_ID,
            'google_sheets_production_students_tab' => Setting::where('key', 'google_sheets_production_students_tab')->value('value') 
                ?? (Setting::where('key', 'google_sheets_students_tab')->value('value') ?? 'Студенти'),
            'google_sheets_test_students_id'        => Setting::where('key', 'google_sheets_test_students_id')->value('value') 
                ?? '',
            'google_sheets_test_students_tab'       => Setting::where('key', 'google_sheets_test_students_tab')->value('value') 
                ?? '',

            // Таблиця «Дисципліни» (Production & Test)
            'google_sheets_production_subjects_id'  => Setting::where('key', 'google_sheets_production_subjects_id')->value('value') 
                ?? GoogleSheetService::DEFAULT_SUBJECTS_SHEET_ID,
            'google_sheets_production_subjects_tab' => Setting::where('key', 'google_sheets_production_subjects_tab')->value('value') 
                ?? (Setting::where('key', 'google_sheets_subjects_tab')->value('value') ?? 'Всі'),
            'google_sheets_test_subjects_id'        => Setting::where('key', 'google_sheets_test_subjects_id')->value('value') 
                ?? '',
            'google_sheets_test_subjects_tab'       => Setting::where('key', 'google_sheets_test_subjects_tab')->value('value') 
                ?? '',
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Налаштування Google Таблиць';
    }

    public function description(): ?string
    {
        $mode = GoogleSheetService::isTestMode()
            ? '🔴 Активний ТЕСТОВИЙ режим'
            : '🟢 Активний РОБОЧИЙ режим (Production)';
        return "Керування таблицями студентів і дисциплін, назвами аркушів та режимами синхронізації. Поточний статус: {$mode}";
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Зберегти налаштування')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            // Блок 1: Режим роботи
            Layout::block(Layout::rows([
                Switcher::make('google_sheets_test_mode')
                    ->sendTrueOrFalse()
                    ->title('Увімкнути тестовий режим Google Таблиць')
                    ->help('Якщо увімкнено, операції імпорту студентів, дисциплін та посилання на таблиці будуть використовувати тестові налаштування (ID таблиці та/або тестовий аркуш).'),
            ]))
                ->title('⚙️ Режим роботи')
                ->description('Перемикання між робочими даними та тестовим середовищем для перевірки імпорту.'),

            // Блок 2: Таблиця «Студенти» (ЕДЕБО)
            Layout::block(Layout::rows([
                Input::make('google_sheets_production_students_id')
                    ->title('🟢 ID робочої таблиці (Production)')
                    ->help('ID основної робочої таблиці ЄДЕБО. Приклад: <code>1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A</code>')
                    ->placeholder('1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A'),

                Input::make('google_sheets_production_students_tab')
                    ->title('🟢 Назва робочого аркуша (вкладки)')
                    ->help('Назва листа в робочій таблиці (за замовчуванням: <code>Студенти</code>).')
                    ->placeholder('Студенти'),

                Input::make('google_sheets_test_students_id')
                    ->title('🔴 ID тестової таблиці (Test / Staging)')
                    ->help('ID тестової копії таблиці. Якщо поле порожнє — буде використано робочу таблицю з тестовим аркушем.')
                    ->placeholder('Вкажіть ID тестової таблиці (необов\'язково)'),

                Input::make('google_sheets_test_students_tab')
                    ->title('🔴 Назва тестового аркуша (вкладки)')
                    ->help('Назва тестового листа (наприклад: <code>Тест</code>, <code>Студенти_тест</code> або <code>Аркуш1</code>). Якщо порожньо — береться назва робочого аркуша.')
                    ->placeholder('Студенти_тест'),
            ]))
                ->title('🎓 Таблиця «Студенти» (ЕДЕБО)')
                ->description('Синхронізація карток студентів, спеціальностей та академічних груп. Можна використовувати окрему таблицю або окремий аркуш в тій самій таблиці.'),

            // Блок 3: Таблиця «Дисципліни»
            Layout::block(Layout::rows([
                Input::make('google_sheets_production_subjects_id')
                    ->title('🟢 ID робочої таблиці (Production)')
                    ->help('ID основного каталогу вибіркових компонентів. Приклад: <code>1DeCO1hKHqcYPcriPcaIz3LAZVCFKpmfjdkspNu1Is2w</code>')
                    ->placeholder('1DeCO1hKHqcYPcriPcaIz3LAZVCFKpmfjdkspNu1Is2w'),

                Input::make('google_sheets_production_subjects_tab')
                    ->title('🟢 Назва робочого аркуша (вкладки)')
                    ->help('Назва листа в робочій таблиці (за замовчуванням: <code>Всі</code>).')
                    ->placeholder('Всі'),

                Input::make('google_sheets_test_subjects_id')
                    ->title('🔴 ID тестової таблиці (Test / Staging)')
                    ->help('ID тестової копії каталогу. Якщо поле порожнє — буде використано робочу таблицю з тестовим аркушем.')
                    ->placeholder('Вкажіть ID тестової таблиці (необов\'язково)'),

                Input::make('google_sheets_test_subjects_tab')
                    ->title('🔴 Назва тестового аркуша (вкладки)')
                    ->help('Назва тестового листа (наприклад: <code>Всі_тест</code> або <code>Тест</code>). Якщо порожньо — береться назва робочого аркуша.')
                    ->placeholder('Всі_тест'),
            ]))
                ->title('📚 Таблиця «Дисципліни»')
                ->description('Каталог вибіркових навчальних дисциплін та освітніх компонентів. Можна протестувати імпорт з окремого тестового аркуша або окремої таблиці.'),
        ];
    }

    public function save(Request $request)
    {
        $keys = [
            'google_sheets_test_mode'               => $request->input('google_sheets_test_mode') ? '1' : '0',

            'google_sheets_production_students_id'  => trim((string)$request->input('google_sheets_production_students_id')),
            'google_sheets_production_students_tab' => trim((string)$request->input('google_sheets_production_students_tab')) ?: 'Студенти',
            'google_sheets_test_students_id'        => trim((string)$request->input('google_sheets_test_students_id')),
            'google_sheets_test_students_tab'       => trim((string)$request->input('google_sheets_test_students_tab')),

            'google_sheets_production_subjects_id'  => trim((string)$request->input('google_sheets_production_subjects_id')),
            'google_sheets_production_subjects_tab' => trim((string)$request->input('google_sheets_production_subjects_tab')) ?: 'Всі',
            'google_sheets_test_subjects_id'        => trim((string)$request->input('google_sheets_test_subjects_id')),
            'google_sheets_test_subjects_tab'       => trim((string)$request->input('google_sheets_test_subjects_tab')),
        ];

        foreach ($keys as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $mode = $keys['google_sheets_test_mode'] === '1' ? '🔴 Тестовий' : '🟢 Робочий';
        Toast::info("Налаштування Google Таблиць успішно збережено. Активний режим: {$mode}.");
    }
}
