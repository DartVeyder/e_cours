<?php

namespace App\Orchid\Layouts\Student;

use App\Models\UserSpecialty;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
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
            TD::make('id','№') ,
            TD::make('subjects_count','Кількість вибрано')
                ->sort(),
            TD::make('full_name','ПІБ')
            ->filter(TD::FILTER_TEXT)
            ->sort()

            ->render(function ($student){
                return Button::make($student->full_name)
                    ->style(($student->user_id) ? 'background-color: #10ff0a75;color: #005a00;border-radius: 5px;' : '')
                    ->method('chooseStudent', [
                        'studentId' => $student->id,
                        'studentName' => $student->full_name,
                    ]);
            }),
            TD::make('group','Група')
                ->sort()
                ->render(function ($student){
                    if(!empty($student->group)){
                        return Link::make($student->group)
                            ->route('platform.students.group', ['group' =>$student->group]);
                    }

                })
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('group','group')),
            TD::make('card_id','ЄДЕБО')
                ->filter(TD::FILTER_TEXT)

                ->sort(),
            TD::make('email','Email')
                ->filter(TD::FILTER_TEXT)

                ->sort(),
            TD::make('study_form','Форма навчання')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('study_form','study_form')),

            TD::make('degree','Рівень освіти')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('degree','degree')),
            TD::make('department','Факультет')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('department','department')),
            TD::make('specialty','Спеціальність')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('specialty','specialty')),
            TD::make('education_program','Освітня програма')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('education_program','education_program')),
            TD::make('gender','Стать')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('gender','gender')),


        ];
    }
}
