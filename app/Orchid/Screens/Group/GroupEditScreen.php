<?php
namespace App\Orchid\Screens\Group;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupEditScreen extends Screen
{
    public $group;

    protected function checkDepartmentAccess(Group $group): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $user->loadMissing(['department', 'roles']);

        if ($user->roles->contains('slug', 'dekanat') && $user->department_id) {
            if ($group->exists && $group->department_id && $group->department_id !== $user->department_id) {
                abort(403, 'У вас немає прав на редагування групи іншого факультету/підрозділу.');
            }
        }
    }

    public function query(Group $group): iterable
    {
        $this->checkDepartmentAccess($group);

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

    public function permission(): ?iterable
    {
        return [
            'platform.systems.groups',
        ];
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
                ->min(0)
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
        $this->checkDepartmentAccess($group);

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

        activity()
            ->causedBy(Auth::user())
            ->performedOn($group)
            ->withProperties([
                'group_name'     => $group->name,
                'semester_count' => $group->semester_count,
                'limits'         => $semesterLimits,
            ])
            ->log("Збережено групу «{$group->name}»");

        return redirect()->route('platform.groups.edit', $group->id);
    }
}
