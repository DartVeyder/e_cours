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
       // dd(Auth::user()->department);

        $user = Auth::user()->load(['department', 'roles']);

        $specialtiesQuery = UserSpecialty::filters()
            ->withCount('subjects');

        if ($user && $user->department && $user->roles->contains('slug', 'dekanat')) {
            $specialtiesQuery->where('department', $user->department->name);
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

            $row = array_intersect_key($row, array_flip($allowed));

            if ($row['study_status'] != "Відраховано") {
                $userSpecialty = UserSpecialty::updateOrCreate(
                    ['card_id' => $row['card_id']],
                    $row
                );
            } else {
                // Якщо статус "Відраховано" і запис існує — видаляємо
                UserSpecialty::where('card_id', $row['card_id'])->delete();
            }

        }

        Toast::success("Студентів імпортовано");
        activity()
            ->causedBy(Auth::user())
            ->log("Імпорт студентів із Google Sheet завершено");
        return;
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
