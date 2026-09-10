<?php

namespace App\Orchid\Filters;

use App\Models\UserSpecialty;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class EntryYearFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Рік вступу';
    }

    /**
     * @return string
     */
    public function value(): string
    {
        $value = $this->getFilterValue();
        return $this->name() . ': ' . ($value ?? 'Всі');
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return ['entry_year', 'study_start', 'filter.study_start'];
    }

    protected function getFilterValue(): ?string
    {
        $val = $this->request->get('entry_year') 
            ?? $this->request->get('study_start') 
            ?? $this->request->input('filter.study_start');

        if (is_array($val)) {
            $val = reset($val);
        }

        return !empty($val) ? (string) $val : null;
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
        $value = $this->getFilterValue();

        if (!empty($value)) {
            return $builder->where('user_specialties.study_start', 'like', $value . '%');
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
            Select::make('entry_year')
                ->options(UserSpecialty::getEntryYearsOptions())
                ->empty('Всі роки вступу')
                ->title('Рік вступу')
                ->value($this->request->get('entry_year')),
        ];
    }
}
