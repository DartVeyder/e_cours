<?php

namespace App\Orchid\Layouts\Subject;

use App\Models\Subject;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SubjectListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'subjects';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [

            TD::make('id','ID')
                ->sort()
                ->width('70px'),
            TD::make('users_count','Кількість вибрало')
                ->sort(),
            TD::make('name','Дисципліна')
                ->filter(TD::FILTER_TEXT)
                ->render(function ($subject) {
                    return Link::make($subject->name)
                        ->style('width:150px; text-wrap:wrap;')
                        ->route('platform.subjects.specialty', $subject->id);
                })

                ->sort()
            ,
            TD::make('chair','Кафедра')
                ->filter( TD::FILTER_SELECT,Subject::distinct()->pluck('chair','chair'))

                ->sort(),
            TD::make('annotation','Анотація')
                ->render(function ($subject) {
                    if (empty($subject->annotation)) {
                        return '';
                    }
                    return Link::make()
                        ->icon('fa.file-pdf')
                        ->href($subject->annotation)
                        ->style('font-size:20px;')
                        ->target('_blank') ;
                }),
            TD::make('control_type','Вид контролю'),
            TD::make('credits','Кількість кредитів'),
            TD::make('status','Статус дисципліни'),
            TD::make('study_semester','Вивчення у семестрі'),
            TD::make('max_min_students','Макс/мін. кількість здобувачів')
                ->sort(),
            TD::make('not_for_op','Для яких ОП не може читатися'),
            TD::make('code','Шифр')->sort(),
            TD::make('education_level','Рівень освіти')
                ->filter( TD::FILTER_SELECT,Subject::distinct()
                    ->pluck('education_level','education_level'))
                ->sort(),
            TD::make('active','Активний')->sort(),

        ];
    }
}
