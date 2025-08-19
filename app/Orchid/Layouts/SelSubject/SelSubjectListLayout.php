<?php

namespace App\Orchid\Layouts\SelSubject;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SelSubjectListLayout extends Table
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
            TD::make('check','')
                ->render(function ($subject) {
                    return Button::make('Вибрати')
                        ->method('chooseSubject', [
                            'subjectId' => $subject->id,
                        ]);
                }),
            TD::make('id','ID')
                ->sort()
                ->width('70px'),
            TD::make('name','Дисципліна')
                ->sort(),
            TD::make('department','Кафедра')
                ->sort(),
            TD::make('annotation','Анотація')
                ->render(function ($subject) {
                return Link::make()
                    ->icon('fa.file-pdf')
                    ->href($subject->annotation)
                    ->target('_blank') ;
            }),
            TD::make('control_type','Вид контролю'),
            TD::make('credits','Кількість кредитів'),
            TD::make('status','Статус дисц. (загальної підготовки (ЗП) чи професійно-орієнтована (ПО))'),
            TD::make('semester','Вивчення у семестрі'),
            TD::make('max_min_students','Макс/мін. кількість здобувачів')
                ->sort(),
            TD::make('not_for_op','Для яких ОП не може читатися'),
        ];
    }
}
