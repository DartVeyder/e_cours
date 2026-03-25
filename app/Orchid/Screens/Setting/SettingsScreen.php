<?php

namespace App\Orchid\Screens\Setting;

use App\Models\Setting;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SettingsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'subject_selection_enabled' => Setting::where('key', 'subject_selection_enabled')->value('value') !== '0',
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Налаштування системи';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Зберегти')
                ->icon('bs.save')
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
            Layout::rows([
                Switcher::make('subject_selection_enabled')
                    ->sendTrueOrFalse()
                    ->title('Дозволити студентам вибирати дисципліни')
                    ->help('При вимкненні студенти не зможуть вибирати або скасовувати вибір дисциплін.'),
            ])
        ];
    }

    public function save(Request $request)
    {
        $enabled = $request->input('subject_selection_enabled') ? '1' : '0';

        Setting::updateOrCreate(
            ['key' => 'subject_selection_enabled'],
            ['value' => $enabled]
        );

        Toast::info('Налаштування збережено.');
    }
}
