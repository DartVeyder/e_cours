<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Group;

use App\Models\Degree;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class GroupDegreeFilter extends Filter
{
    public function name(): string
    {
        return 'Рівень освіти';
    }

    public function parameters(): array
    {
        return ['degree_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('degree_id', $this->request->get('degree_id'));
    }

    public function display(): array
    {
        return [
            Select::make('degree_id')
                ->options(
                    Degree::orderBy('name')->pluck('name', 'id')->toArray()
                )
                ->empty('Всі рівні', '')
                ->value($this->request->get('degree_id'))
                ->title('Рівень освіти'),
        ];
    }
}
