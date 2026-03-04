<?php

namespace App\Orchid\Layouts\Group;

use App\Models\Degree;
use App\Models\Department;
use App\Models\Group;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class GroupListLayout extends Table
{
    protected $target = 'groups';

    protected function columns(): iterable
    {
        return [
            TD::make('№')
                ->render(function (Group $model, object $loop) {
                    return $loop->iteration;
                }),

            TD::make('name', 'Група')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(function (Group $group) {
                    return Link::make($group->name)
                        ->route('platform.students.group', ['group' => $group->name]);
                }),

            TD::make('department_id', 'Факультет')
                ->sort() 
                ->filter( TD::FILTER_SELECT,Department::orderBy('name')->pluck('name', 'id')->toArray())
                ->render(fn(Group $group) => optional($group->department)->name ?? '—'),

            TD::make('degree_id', 'Рівень освіти')
                ->sort() 
                ->filter( TD::FILTER_SELECT,Degree::orderBy('name')->pluck('name', 'id')->toArray())
                ->render(fn(Group $group) => optional($group->degree)->name ?? '—'),

            TD::make('semester_count', 'Семестрів')
                ->align(TD::ALIGN_CENTER),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('80px')
                ->render(fn(Group $group) =>
                    Link::make()
                        ->route('platform.groups.edit', $group->id)
                        ->icon('bs.pencil')),
        ];
    }
}
