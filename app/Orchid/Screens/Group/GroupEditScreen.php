<?php
namespace App\Orchid\Screens\Group;

use App\Models\Group;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupEditScreen extends Screen
{
    public $group;

    public function query(Group $group): iterable
    {
        $group->load('semesterLimits');
        $this->group = $group; // зберігаємо для name()
        return [
            'group' => $group
        ];
    }

    public function name(): ?string
    {
        return $this->group ? 'Група ' . $this->group->name : 'Нова група';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Зберегти'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        $group = $this->group;
        $semesterCount = $group?->semester_count ?? 1;

        $semesterInputs = [];
        for ($i = 1; $i <= $semesterCount; $i++) {
            $semesterInputs[] = Input::make("semester_limits.$i.max_subjects")
                ->type('number')
                ->min(1)
                ->step(1)
                ->title("Кількість предметів для семестру $i")
                ->placeholder('Вкажіть кількість предметів')
                ->value($group?->semesterLimits->firstWhere('semester', $i)?->max_subjects ?? 0);
        }

        return [
            Layout::rows([
                Input::make('group.semester_count')
                    ->type('number')
                    ->min(0)
                    ->step(1)
                    ->title('Кількість семестрів')
                    ->required(),
            ])->title('Основна інформація'),

            Layout::rows($semesterInputs)->title('Ліміти предметів по семестрах'),
        ];
    }

    public function save(Request $request, Group $group)
    {
        // Зберігаємо дані групи
        $group->fill($request->get('group'))->save();

        // Зберігаємо ліміти предметів
        $semesterLimits = $request->get('semester_limits', []);
        $group->semesterLimits()->delete();

        foreach ($semesterLimits as $semester => $limitData) {
            if (!empty($limitData['max_subjects'])) {
                $group->semesterLimits()->updateOrCreate(
                    ['semester' => $semester],
                    ['max_subjects' => (int)$limitData['max_subjects']]
                );
            }
        }

        Toast::info("Групу успішно збережено.");

        return redirect()->route('platform.groups.edit', $group->id);
    }
}
