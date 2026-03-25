<?php

namespace App\Orchid\Layouts\Student;

use App\Orchid\Filters\SubjectSelectionFilter;
use Orchid\Screen\Layouts\Selection;

class StudentFiltersLayout extends Selection
{
    /**
     * @return string[]|iterable
     */
    public function filters(): iterable
    {
        return [
            SubjectSelectionFilter::class,
        ];
    }
}
