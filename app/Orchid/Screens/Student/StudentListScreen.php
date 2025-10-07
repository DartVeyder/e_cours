<?php

namespace App\Orchid\Screens\Student;

use App\Models\Student;
use App\Models\UserSpecialty;
use App\Orchid\Layouts\Student\StudentListLayout;
use App\Services\GoogleSheet\StudentsSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class StudentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {


        $user = Auth::user()->load(['department','degree', 'roles']);

        $specialtiesQuery = UserSpecialty::filters()
            ->withCount('subjects');

        if ($user && $user->department && $user->roles->contains('slug', 'dekanat')) {
            $specialtiesQuery->where('department', $user->department->name);
        }

        if ($user && $user->degree){
            $specialtiesQuery->where('degree', $user->degree->name);
        }




        return [
            'students' => $specialtiesQuery->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Студенти';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Загрузити студентів')
                ->method('importStudentsFromGoogleSheet'),
            Link::make('Google Sheet')
                ->target('_blank')
                ->href("https://docs.google.com/spreadsheets/d/1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A/edit?usp=sharing")
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            StudentListLayout::class,
        ];
    }

    public function importStudentsFromGoogleSheet()
    {
        $studentsSheet = new StudentsSheet();

        foreach ($studentsSheet->readAssoc() as $row)
        {
            // Отримуємо список колонок таблиці user_specialties
            $allowed = Schema::getColumnListing('user_specialties');

            // Визначаємо degree_id, якщо в рядку є назва рівня освіти
            if (!empty($row['degree'])) {
                $degree = \App\Models\Degree::where('name', $row['degree'])->first();
                $row['degree_id'] = $degree ? $degree->id : null;
            }

            // Визначаємо department_id, якщо в рядку є назва департаменту
            if (!empty($row['department'])) {
                $department = \App\Models\Department::where('name', $row['department'])->first();
                $row['department_id'] = $department ? $department->id : null;
            }

            // Залишаємо тільки дозволені колонки таблиці
            $row = array_intersect_key($row, array_flip($allowed));

            $userSpecialty = UserSpecialty::withTrashed()->firstWhere('card_id', $row['card_id']);

            if ($row['study_status'] == "Зараховано") {
                if ($userSpecialty) {
                    // Оновлюємо існуючий запис і відновлюємо, якщо був soft-deleted
                    $userSpecialty->update($row);
                    if ($userSpecialty->trashed()) {
                        $userSpecialty->restore();
                    }
                } else {
                    // Створюємо новий запис
                    UserSpecialty::create($row);
                }
            } elseif ($row['study_status'] == "Відраховано") {
                // Soft delete, якщо запис існує
                if ($userSpecialty && !$userSpecialty->trashed()) {
                    $userSpecialty->delete();
                }
            }
        }

        Toast::success("Студентів імпортовано");
        activity()
            ->causedBy(Auth::user())
            ->log("Імпорт студентів із Google Sheet завершено");
    }


    public function chooseStudent($studentId,$studentName)
    {
        Cookie::queue('user_specialty_id', $studentId, 1440);
        Toast::success("Вибрали $studentName");

        activity()
            ->causedBy(Auth::user()) // адміністратор
            ->withProperties([
                'student_id' => $studentId,
                'student_name' => $studentName,
                'chosen_by_admin' => true
            ])
            ->log("Адміністратор вибрав студента: {$studentName}");
        return redirect()->route('platform.selsubjects');
    }
}
