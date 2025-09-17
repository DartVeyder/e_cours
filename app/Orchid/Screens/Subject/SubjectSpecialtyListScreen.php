<?php

namespace App\Orchid\Screens\Subject;

use App\Models\Subject;
use App\Orchid\Layouts\Subject\SubjecSpecialtytListLayout;
use Orchid\Screen\Screen;

class SubjectSpecialtyListScreen extends Screen
{
    private $subjectName;
    private $countSpecialties;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Subject $subject): iterable
    {
        $subjectUserSpecialties = Subject::with(['userSpecialties' => function($query) {
            $query->filters()->select('user_specialties.id', 'user_specialties.full_name', 'user_specialties.specialty', 'user_specialties.group','user_specialties.study_form');
        }])->find($subject->id);
         $this->subjectName = $subjectUserSpecialties->name;
         $this->countSpecialties = $subjectUserSpecialties->userSpecialties->count();
        return [
            'userSpecialties' => $subjectUserSpecialties->userSpecialties
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->subjectName;
    }
    public function description(): ?string
    {
        return "Всього вибрало: ". $this->countSpecialties;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            SubjecSpecialtytListLayout::class,
        ];
    }
}
