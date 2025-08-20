<?php

namespace App\Orchid\Screens\Subject;

use App\Models\Subject;
use App\Orchid\Layouts\Subject\SubjectListLayout;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;

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
                ->icon('reload')
                ->method('importFromGoogleSheet'),
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
                ['name' => $row['name']], // перевірка унікальності по name
                [
                    'department' => $row['department'] ?? null,
                    'annotation' => $row['annotation'] ?? null,
                    'control_type' => $row['control_type'] ?? null,
                    'credits' => $row['credits'] ?? null,
                    'status' => $row['status'] ?? null,
                    'semester' => $row['semester'] ?? null,
                    'max_min_students' => $row['max_min_students'] ?? null,
                    'not_for_op' => $row['not_for_op'] ?? null,
                ]
            );
        }
    }

}
