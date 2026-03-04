<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Group;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class GroupNameFilter extends Filter
{
    public function name(): string
    {
        return 'Назва групи';
    }

    public function parameters(): array
    {
        return ['name'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('name', 'like', '%' . $this->request->get('name') . '%');
    }

    public function display(): array
    {
        return [
            Input::make('name')
                ->type('text')
                ->value($this->request->get('name'))
                ->placeholder('Пошук по назві групи...')
                ->title('Назва групи'),
        ];
    }
}
