<?php

namespace App\Orchid\Layouts\SelSubject;

use App\Models\Subject;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Select;
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
            TD::make('is_selected','Вибрано')
                ->sort()
                ->render(function ($subject) {

                    return Button::make('')
                        ->icon(($subject->is_selected)? 'fa.check-square': 'fa.square')
                        ->style( ($subject->is_student_choice == 1) ? 'color:#0d6efd;font-size:20px;' : 'color:red;font-size:20px;')
                        ->style(is_null($subject->is_student_choice)
                            ? 'font-size:20px;'                     // якщо немає вибору
                            : ($subject->is_student_choice ? 'color:#0d6efd;font-size:20px;' : 'color:red;font-size:20px;'))
                        ->method('chooseSubject', [
                            'subjectId' => $subject->id,
                            'subjectName' => $subject->name,
                        ]);
                })->canSee(!empty( request()->cookie('user_specialty_id'))) ,
            TD::make('id','ID')
                ->sort()
                ->width('70px'),

            TD::make('name','Дисципліна')
                ->filter(TD::FILTER_TEXT)
                ->sort(),
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
                    ->href($subject->annotation  )
                    ->style('font-size:20px;')
                    ->target('_blank') ;
            }
            ),
            TD::make('control_type','Вид контролю'),
            TD::make('credits','Кількість кредитів'),
            TD::make('status','Статус дисципліни'),
            TD::make('semester','Вивчення у семестрі'),
            TD::make('max_min_students','Макс/мін. кількість здобувачів')
                ->sort(),
            TD::make('not_for_op','Для яких ОП не може читатися'),
            TD::make('code','Шифр'),
        ];
    }
}
