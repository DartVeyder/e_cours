<?php

namespace App\Orchid\Screens\Group;

use App\Models\UserSpecialty;
use App\Orchid\Layouts\Group\GroupListLayout;
use App\Services\GoogleSheet\ReportStudentsGroupSheet;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupListScreen extends Screen
{
    public $groups;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        return [
            'groups' => UserSpecialty::select('group')
                ->distinct()
                ->orderBy('group')
                ->get()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Групи';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Вигрузити звіт по групам')
                ->method('exportReportStudentsGroupToGoogleSheet'),
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
            GroupListLayout::class
        ];
    }

    public function exportReportStudentsGroupToGoogleSheet()
    {
        $reportStudentsGroupSheet = new ReportStudentsGroupSheet();

        foreach ($this->groups as $item){
            $dataSheet = [];
            $newSheetId = $reportStudentsGroupSheet->createSheet( $item->group);
            $students = UserSpecialty::with(['subjects' => function ($query) {
                $query->select('subjects.id', 'subjects.name');
            }])
                ->where('group', $item->group)
                ->get()
                ->map(function ($student) {
                    $student->subjects = $student->subjects->pluck('name')->toArray();
                    return $student;
                });
            $dataSheet[] =  [ $item->group, date("Y-m-d H:i:s")];
            foreach ($students as $student) {
                // об’єднуємо full_name з subjects
                $row = array_merge(
                    [$student->full_name], // перший елемент – ім’я студента
                    $student->subjects     // масив предметів (назви)
                );

                $dataSheet[] = $row;


            }

            $reportStudentsGroupSheet->writeBySheetId($newSheetId, $dataSheet);

        }
        Toast::success("Звіт по групам вигружено в гугл таблицю");
        return;
    }
}
