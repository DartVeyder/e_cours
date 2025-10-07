<?php

namespace App\Orchid\Screens\Student;

use App\Models\UserSpecialty;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class StudentsGroupScreen extends Screen
{
    public $group;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query($group): iterable
    {
        $this->group = $group;

        $user = Auth::user()->load(['department', 'degree', 'roles']);

        $studentsQuery = UserSpecialty::with('subjects')
            ->where('group', $group);

        // Фільтр для деканату
        if ($user && $user->roles->contains('slug', 'dekanat')) {
            if ($user->department) {
                $studentsQuery->where('department', $user->department->name);
            }
        }

        if ($user->degree) {
            $studentsQuery->where('degree', $user->degree->name); // ✅ фільтр по degree
        }

        $students = $studentsQuery->get();

        return [
            'students' => $students
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return "Група {$this->group}";
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
            Layout::view('student.students_group')
        ];
    }
}
