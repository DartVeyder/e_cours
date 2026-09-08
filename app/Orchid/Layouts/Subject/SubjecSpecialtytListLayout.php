<?php

namespace App\Orchid\Layouts\Subject;

use App\Models\Subject;
use App\Models\UserSpecialty;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SubjecSpecialtytListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'userSpecialties';

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
                    return $loop->iteration; // повертає 1,2,3…
                }),
            TD::make('full_name','ПІБ')
                ->filter(TD::FILTER_TEXT)
                ->sort()
                ->render(function ($student) {
                    $name = htmlspecialchars($student->full_name ?? '', ENT_QUOTES, 'UTF-8');
                    return "<div class=\"d-inline-flex align-items-center gap-1 position-relative\" onmouseenter=\"const b=this.querySelector('.copy-pib-btn');if(b)b.style.opacity='1'\" onmouseleave=\"const b=this.querySelector('.copy-pib-btn');if(b)b.style.opacity='0'\">"
                        . "<span>{$name}</span>"
                        . "<button type=\"button\" class=\"btn btn-sm btn-light border-0 p-1 rounded-circle copy-pib-btn text-secondary\" style=\"opacity: 0; transition: opacity 0.2s ease-in-out, background 0.15s ease; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; background: rgba(0,0,0,0.06);\" title=\"Скопіювати ПІБ\" onmouseenter=\"this.style.background='rgba(0,0,0,0.12)'\" onmouseleave=\"this.style.background='rgba(0,0,0,0.06)'\" onclick=\"event.stopPropagation(); navigator.clipboard.writeText('{$name}'); const orig=this.innerHTML; this.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'13\' height=\'13\' fill=\'#198754\' viewBox=\'0 0 16 16\'><path d=\'M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\'/></svg>'; setTimeout(()=>{ this.innerHTML=orig; }, 1500);\">"
                        . "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"13\" height=\"13\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z\"/><path d=\"M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z\"/></svg>"
                        . "</button>"
                        . "</div>";
                }),
            TD::make('specialty','Спеціальність')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('specialty','specialty')),
            TD::make('group_name','Група')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('group_name','group_name')),
            TD::make('study_form','Форма навчання')
                ->sort()
                ->filter( TD::FILTER_SELECT,UserSpecialty::distinct()->pluck('study_form','study_form')),
            TD::make('semester','Семестр')
                ->render(function (UserSpecialty $model) {
                    return $model->pivot->semester ?? '—';
                }),
        ];
    }
}
