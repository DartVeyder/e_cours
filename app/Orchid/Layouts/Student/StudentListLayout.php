<?php

namespace App\Orchid\Layouts\Student;

use App\Models\UserSpecialty;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class StudentListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'students';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('№')
                ->render(function (Model $model, object $loop) {
                    return $loop->iteration;
                }),

            TD::make('full_name','ПІБ')
            ->filter(TD::FILTER_TEXT)
            ->sort()
            ->render(function ($student){
                return Button::make($student->full_name)

                    ->method('chooseStudent', [
                        'studentId' => $student->id,
                        'studentName' => $student->full_name,
                    ]);
            }),
            TD::make('degree','Рівень освіти'),
            TD::make('department','Структурний підрозділ'),
            TD::make('specialty','Спеціальність'),
            TD::make('education_program','Освітня програма'),
            TD::make('gender','Стать'),
            TD::make('study_form','Форма навчання'),
            TD::make('group','Група')
        ];
    }
}
