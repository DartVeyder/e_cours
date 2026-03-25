<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class SubjectSelectionFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Стан вибору дисциплін';
    }

    /**
     * @return string
     */
    public function value(): string
    {
        $value = $this->request->get('subject_selection');
        $labels = [
            'all'     => 'Всі обрані',
            'partial' => 'Частково обрані',
            'none'    => 'Не обрані',
        ];

        return $this->name() . ': ' . ($labels[$value] ?? $value);
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return ['subject_selection'];
    }

    /**
     * Apply to a given Eloquent query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        $value = $this->request->get('subject_selection');

        $maxSubjectsSubquery = '(SELECT COALESCE(SUM(max_subjects), 0) FROM group_semester_limits WHERE group_semester_limits.group_id = user_specialties.group_id)';

        if ($value === 'all') {
            return $builder->having('subjects_count', '>', 0)
                           ->havingRaw("subjects_count >= $maxSubjectsSubquery");
        }

        if ($value === 'partial') {
            return $builder->having('subjects_count', '>', 0)
                           ->havingRaw("subjects_count < $maxSubjectsSubquery");
        }

        if ($value === 'none') {
            return $builder->having('subjects_count', '=', 0);
        }

        return $builder;
    }

    /**
     * Get the display fields.
     *
     * @return Field[]
     */
    public function display(): iterable
    {
        return [
            Select::make('subject_selection')
                ->options([
                    'all'     => 'Всі обрані',
                    'partial' => 'Частково обрані',
                    'none'    => 'Не обрані',
                ])
                ->empty('Всі студенти')
                ->title('Стан вибору дисциплін')
                ->value($this->request->get('subject_selection'))
        ];
    }
}
