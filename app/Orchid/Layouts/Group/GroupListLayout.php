<?php

namespace App\Orchid\Layouts\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class GroupListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'groups';

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
            TD::make('name','Група')
                ->render(function ($group){
                    return Link::make($group->name)
                        ->route('platform.students.group', ['group' =>$group->name]);

                }),
            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn ($group) =>
                    Link::make()
                        ->route('platform.groups.edit', $group->id)
                        ->icon('bs.pencil')),

        ];
    }
}
