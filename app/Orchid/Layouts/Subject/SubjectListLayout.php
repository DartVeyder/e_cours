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
            TD::make('users_count', 'Кількість вибрало')
                ->sort()
                ->render(function ($subject) {
                    $total = $subject->total_users_count ?? $subject->users_count ?? 0;
                    $yearCounts = $subject->year_counts ?? [];

                    if ($total == 0 && empty($yearCounts)) {
                        return '<span class="text-muted">0</span>';
                    }

                    $activeFilterYear = request()->get('entry_year') ?? request()->input('filter.study_start');
                    if (is_array($activeFilterYear)) {
                        $activeFilterYear = reset($activeFilterYear);
                    }

                    $allUrl = route('platform.subjects.specialty', $subject->id);
                    $badgesHtml = '';

                    foreach ($yearCounts as $year => $count) {
                        $badgeColor = match ((string)$year) {
                            '2026' => 'bg-success',
                            '2025' => 'bg-primary',
                            '2024' => 'bg-info',
                            default => 'bg-secondary',
                        };

                        $isActive = ($activeFilterYear && (string)$year === (string)$activeFilterYear);
                        $activeClass = $isActive ? 'border border-2 border-dark shadow-sm' : '';

                        $yearUrl = route('platform.subjects.specialty', [
                            'subject' => $subject->id,
                            'entry_year' => $year,
                        ]);

                        $displayYear = ($year === '—') ? 'Інші' : $year;

                        $badgesHtml .= "<a href=\"{$yearUrl}\" class=\"badge {$badgeColor} bg-opacity-75 text-white text-decoration-none px-2 py-1 rounded-pill {$activeClass}\" title=\"Переглянути студентів {$displayYear} року вступу ({$count})\">{$displayYear}: {$count}</a>";
                    }

                    $totalTitle = "Переглянути всіх студентів ({$total})";
                    $totalHtml = "<div class=\"d-flex align-items-center gap-1 mb-1\">"
                        . "<span class=\"text-muted small\">Всього:</span> "
                        . "<a href=\"{$allUrl}\" class=\"fw-bold text-dark text-decoration-underline\" title=\"{$totalTitle}\">{$total}</a>"
                        . "</div>";

                    return "<div class=\"py-1\" style=\"min-width: 110px;\">"
                        . $totalHtml
                        . "<div class=\"d-flex flex-wrap gap-1\">"
                        . $badgesHtml
                        . "</div>"
                        . "</div>";
                }),
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
            TD::make('work_program','Робоча програма')
                ->render(function ($subject) {
                if (empty($subject->work_program)) {
                    return '';
                }
                return Link::make()
                    ->icon('fa.file-pdf')
                    ->href($subject->work_program  )
                    ->style('font-size:20px;')
                    ->target('_blank') ;
            }
            ),

        ];
    }
}
