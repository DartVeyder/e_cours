<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Types;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\BaseHttpEloquentFilter;

class StudyStartYearFilter extends BaseHttpEloquentFilter
{
    /**
     * Apply the year filter on study_start column.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        $value = $this->getHttpValue();

        if (empty($value)) {
            return $builder;
        }

        if (is_array($value)) {
            $values = array_filter($value);
            if (empty($values)) {
                return $builder;
            }

            return $builder->where(function (Builder $query) use ($values) {
                foreach ($values as $val) {
                    $valStr = (string) $val;
                    $query->orWhere('user_specialties.' . $this->column, 'like', $valStr . '%')
                          ->orWhere($this->column, 'like', $valStr . '%');
                }
            });
        }

        $valStr = (string) $value;

        return $builder->where(function (Builder $query) use ($valStr) {
            $query->where('user_specialties.' . $this->column, 'like', $valStr . '%')
                  ->orWhere($this->column, 'like', $valStr . '%');
        });
    }
}
