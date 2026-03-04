<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Group;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class GroupDepartmentFilter extends Filter
{
    public function name(): string
    {
        return 'Факультет';
    }

    public function parameters(): array
    {
        return ['department_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('department_id', $this->request->get('department_id'));
    }

    public function display(): array
    {
        return [
            Select::make('department_id')
                ->options(
                    Department::orderBy('name')->pluck('name', 'id')->toArray()
                )
                ->empty('Всі факультети', '')
                ->value($this->request->get('department_id'))
                ->title('Факультет'),
        ];
    }
}
