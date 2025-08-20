<?php

namespace App\Orchid\Layouts\Subject;

use App\Models\Subject;
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
            TD::make('full_name','ПІБ'),
            TD::make('specialty','Спеціальність'),
            TD::make('group','Група')
        ];
    }
}
