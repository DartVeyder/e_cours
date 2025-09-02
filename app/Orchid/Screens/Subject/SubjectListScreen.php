<?php

namespace App\Orchid\Screens\Subject;

use App\Models\Subject;
use App\Orchid\Layouts\Subject\SubjectListLayout;
use App\Services\GoogleSheet\ReportSubjectsStudentsSheet;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class SubjectListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $subjects = Subject::filters()->withCount('users')->paginate();
        return [
            'subjects' =>  $subjects

        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список предметів';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Загрузити дисципліни')
                ->method('importFromGoogleSheet'),
            Button::make('Вигрузити звіт по дисциплінах')
                ->method('exportToGoogleSheet'),
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
            SubjectListLayout::class,
        ];
    }

    public function importFromGoogleSheet()
    {
        $selsubjectSheet = new SelsubjectSheet();

        foreach ($selsubjectSheet->readAssoc() as $row)
        {
            Subject::updateOrCreate(
                ['code' => $row['code']], // перевірка унікальності по name
                $row
            );
        }

       Toast::success("Дисципліни імпортовано");
        return;
    }

    public function exportToGoogleSheet(){
        $reportSubjectsStudentsSheet = new ReportSubjectsStudentsSheet();
        $subjects = Subject::with(['userSpecialties' => function($query) {
            $query->select('user_specialties.full_name', 'user_specialties.specialty', 'user_specialties.group');
        }])->whereHas('userSpecialties')->get();

        foreach ($subjects as $subject) {
            $newSheetId = $reportSubjectsStudentsSheet->createSheet($subject->name);
            $data = $subject->userSpecialties->makeHidden('pivot')->map(fn($item) => array_values($item->toArray()))->toArray();
            array_unshift($data, ['Повне ім’я', 'Спеціальність', 'Група']);

            $reportSubjectsStudentsSheet->writeBySheetId($newSheetId, $data);
        }

        Toast::success("Звіт загружено в гугл таблицю");
        return;
    }

}
