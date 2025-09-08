<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\Department;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserDepartmentLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('user.department_id')
                ->fromModel(Department::class, 'name')
                ->empty('Не вибрано')
                ->title(__('Department'))
                ->help('Please specify which faculty this account should belong to.'),
        ];
    }
}
