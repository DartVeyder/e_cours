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
     * Helper to render content with an interactive hover-to-copy button.
     */
    private function renderWithCopy($content, ?string $textToCopy, string $tooltip = 'Скопіювати'): string
    {
        if (empty($textToCopy)) {
            return (string) $content;
        }

        $escaped = htmlspecialchars($textToCopy, ENT_QUOTES, 'UTF-8');
        $contentHtml = (string) $content;

        return "<div class=\"d-inline-flex align-items-center gap-1 position-relative text-nowrap\" style=\"white-space: nowrap;\" onmouseenter=\"const b=this.querySelector('.copy-hover-btn');if(b)b.style.opacity='1'\" onmouseleave=\"const b=this.querySelector('.copy-hover-btn');if(b)b.style.opacity='0'\">"
            . "<span class=\"text-nowrap\">" . $contentHtml . "</span>"
            . "<button type=\"button\" class=\"btn btn-sm btn-light border-0 p-1 rounded-circle copy-hover-btn text-secondary\" style=\"opacity: 0; transition: opacity 0.2s ease-in-out, background 0.15s ease; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; background: rgba(0,0,0,0.06); flex-shrink: 0; margin-left: 2px;\" title=\"{$tooltip}\" onmouseenter=\"this.style.background='rgba(0,0,0,0.12)'\" onmouseleave=\"this.style.background='rgba(0,0,0,0.06)'\" onclick=\"event.stopPropagation(); navigator.clipboard.writeText('{$escaped}'); const orig=this.innerHTML; this.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'11\' height=\'11\' fill=\'#198754\' viewBox=\'0 0 16 16\'><path d=\'M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\'/></svg>'; setTimeout(()=>{ this.innerHTML=orig; }, 1500);\">"
            . "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"11\" height=\"11\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z\"/><path d=\"M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z\"/></svg>"
            . "</button>"
            . "</div>";
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id','№')
                ->width('80px')
                ->style('white-space: nowrap;')
                ->sort()
                ->align(TD::ALIGN_CENTER),

            TD::make('subjects_count','Кількість вибрано')
                ->sort()
                ->align(TD::ALIGN_CENTER)
                ->style('white-space: nowrap;')
                ->render(function ($student) {
                    $totalRequired = $student->group ? $student->group->semesterLimits->sum('max_subjects') : 0;
                    if ($totalRequired > 0) {
                        return "{$student->subjects_count} / {$totalRequired}";
                    }
                    return $student->subjects_count;
                }),

            TD::make('study_start', 'Рік вступу')
                ->sort()
                ->align(TD::ALIGN_CENTER)
                ->style('white-space: nowrap;')
                ->filter(TD::FILTER_SELECT, UserSpecialty::getEntryYearsOptions())
                ->render(function ($student) {
                    $year = $student->entry_year ?? ($student->study_start ? substr((string)$student->study_start, 0, 4) : '—');
                    if ($year === '—' || empty($year)) {
                        return '<span class="text-muted">—</span>';
                    }
                    $badgeClass = ($year == '2026') ? 'bg-success' : 'bg-primary';
                    return "<span class=\"badge {$badgeClass} bg-opacity-75 text-white fw-semibold px-2 py-1 rounded-pill\">{$year}</span>";
                }),

            TD::make('full_name','ПІБ')
                ->filter(TD::FILTER_TEXT)
                ->sort()
                ->style('white-space: nowrap;')
                ->render(function ($student) {
                    $button = Button::make($student->full_name)
                        ->style(($student->user_id) ? 'background-color: #10ff0a75;color: #005a00;border-radius: 5px;' : '')
                        ->method('chooseStudent', [
                            'studentId' => $student->id,
                            'studentName' => $student->full_name,
                        ]);

                    return $this->renderWithCopy($button, $student->full_name, 'Скопіювати ПІБ');
                }),

            TD::make('group_name','Група')
                ->sort()
                ->style('white-space: nowrap;')
                ->render(function ($student) {
                    if (!empty($student->group_name)) {
                        $link = Link::make($student->group_name)
                            ->route('platform.students.group', ['group' => $student->group_name]);
                        return $this->renderWithCopy($link, $student->group_name, 'Скопіювати назву групи');
                    }
                    return '—';
                })
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('group_name', 'group_name')),

            TD::make('card_id','ЄДЕБО')
                ->filter(TD::FILTER_TEXT)
                ->sort()
                ->style('white-space: nowrap;')
                ->render(function ($student) {
                    if (!empty($student->card_id)) {
                        return $this->renderWithCopy(e($student->card_id), $student->card_id, 'Скопіювати код ЄДЕБО');
                    }
                    return '—';
                }),

            TD::make('email','Email')
                ->filter(TD::FILTER_TEXT)
                ->sort()
                ->style('white-space: nowrap;')
                ->render(function ($student) {
                    if (!empty($student->email)) {
                        return $this->renderWithCopy(e($student->email), $student->email, 'Скопіювати Email');
                    }
                    return '—';
                }),

            TD::make('study_form','Форма навчання')
                ->sort()
                ->style('white-space: nowrap;')
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('study_form', 'study_form')),

            TD::make('degree','Рівень освіти')
                ->sort()
                ->style('white-space: nowrap;')
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('degree', 'degree')),

            TD::make('department','Факультет')
                ->sort()
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('department', 'department')),

            TD::make('specialty','Спеціальність')
                ->sort()
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('specialty', 'specialty')),

            TD::make('education_program','Освітня програма')
                ->sort()
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('education_program', 'education_program')),

            TD::make('gender','Стать')
                ->sort()
                ->align(TD::ALIGN_CENTER)
                ->style('white-space: nowrap;')
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('gender', 'gender')),

            TD::make('study_status', 'Статус навчання')
                ->sort()
                ->style('white-space: nowrap;')
                ->filter(TD::FILTER_SELECT, UserSpecialty::distinct()->pluck('study_status', 'study_status'))
                ->render(function ($student) {
                    if (empty($student->study_status)) {
                        return '<span class="text-muted">—</span>';
                    }
                    $color = match ($student->study_status) {
                        'Зараховано'          => 'success',
                        'Змінено фінансування' => 'info',
                        'Відраховано'         => 'danger',
                        default               => 'secondary',
                    };
                    return "<span class=\"badge bg-{$color} bg-opacity-75\">{$student->study_status}</span>";
                }),
        ];
    }
}
