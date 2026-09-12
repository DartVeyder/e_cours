<?php

namespace App\Orchid\Layouts\Subject;

use App\Orchid\Filters\EntryYearFilter;
use Orchid\Screen\Layouts\Selection;

class SubjectFiltersLayout extends Selection
{
    /**
     * @return string[]|iterable
     */
    public function filters(): iterable
    {
        return [
            EntryYearFilter::class,
        ];
    }
}
