<?php

namespace App\Orchid\Layouts\Group;

use Illuminate\Database\Eloquent\Model;
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
            TD::make('group','Група')
                ->render(function ($student){
                    return Link::make($student->group)
                        ->route('platform.students.group', ['group' =>$student->group]);

                })
        ];
    }
}
