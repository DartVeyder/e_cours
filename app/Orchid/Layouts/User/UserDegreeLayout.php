<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\Degree;
use App\Models\Department;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserDegreeLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('user.degree_id')
                ->fromModel(Degree::class, 'name')
                ->empty('Не вибрано')
                ->title(__('Degree'))
                ->help('Please specify what level of education this account should belong to.'),
        ];
    }
}
