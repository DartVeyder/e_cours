<?php

namespace App\Orchid\Screens\Group;

use App\Models\Group;
use App\Models\UserSpecialty;
use App\Orchid\Layouts\Group\GroupListLayout;
use App\Services\GoogleSheet\ReportStudentsGroupSheet;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class GroupListScreen extends Screen
{
    public $groups;

    public function query(): iterable
    {
        $user = Auth::user()->load(['department', 'degree', 'roles']);

        $groups = Group::with(['department', 'degree'])->filters();

        // Фільтр для деканату
        if ($user && $user->roles->contains('slug', 'dekanat')) {
            if ($user->department) {
                $groups->where('department_id', $user->department->id);
            }
        }

        if ($user && $user->degree) {
            $groups->where('degree_id', $user->degree->id);
        }

        return [
            'groups' => $groups->defaultSort('name')->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Групи';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Детальний звіт по студентах (всі групи)')
                ->icon('cloud-download')
                ->method('exportSubjects')
                ->rawClick(),
            // Button::make('Вигрузити звіт по групам')
            //     ->method('exportReportStudentsGroupToGoogleSheet'),
            Link::make('Google Sheet')
                ->target('_blank')
                ->href("https://docs.google.com/spreadsheets/d/1husYxpQlRPIiQEvleTuzXGzukThXTEodX9ZD0kVnY_M/edit?usp=sharing"),
        ];
    }

    public function layout(): iterable
    {
        return [
            GroupListLayout::class,
        ];
    }

    public function exportReportStudentsGroupToGoogleSheet()
    {
        $reportStudentsGroupSheet = new ReportStudentsGroupSheet();

        foreach ($this->groups as $item) {
            $dataSheet = [];
            $newSheetId = $reportStudentsGroupSheet->createSheet($item->group_name);
            $students = UserSpecialty::with(['subjects' => function ($query) {
                $query->select('subjects.id', 'subjects.name');
            }])
                ->where('group_name', $item->group_name)
                ->get()
                ->map(function ($student) {
                    $student->subjects = $student->subjects->pluck('name')->toArray();
                    return $student;
                });
            $dataSheet[] = [$item->group_name, date("Y-m-d H:i:s")];
            foreach ($students as $student) {
                $row = array_merge(
                    [$student->full_name],
                    (array) $student->subjects
                );
                $dataSheet[] = $row;
            }
            $reportStudentsGroupSheet->writeBySheetId($newSheetId, $dataSheet);
        }
        Toast::success("Звіт по групам вигружено в гугл таблицю");
        return;
    }

    public function exportSubjects()
    {
        $user = Auth::user()->load(['department', 'degree', 'roles']);

        // Fetch students directly with same filters as groups
        $specialtiesQuery = UserSpecialty::with([
                'subjects' => function ($q) {
                    $q->select('subjects.id', 'subjects.name')->orderBy('subjects.name');
                }
            ])
            ->orderBy('group_name')
            ->orderBy('full_name');

        if ($user && $user->department && $user->roles->contains('slug', 'dekanat')) {
            $specialtiesQuery->where('department', $user->department->name);
        }

        if ($user && $user->degree) {
            $specialtiesQuery->where('degree', $user->degree->name);
        }

        $students = $specialtiesQuery->get();

        activity()
            ->causedBy(Auth::user())
            ->log("Експорт детального звіту студентів у Excel (зі сторінки Груп)");

        $filename = "Детальний_звіт_Студенти_" . date('Y-m-d') . '.xlsx';
        return (new \App\Services\GroupExcelExport())->exportCollection($students, 'Студенти', $filename, true);
    }
}
