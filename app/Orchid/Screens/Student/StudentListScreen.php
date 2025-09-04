<?php

namespace App\Orchid\Screens\Student;

use App\Models\Student;
use App\Models\UserSpecialty;
use App\Orchid\Layouts\Student\StudentListLayout;
use App\Services\GoogleSheet\StudentsSheet;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Orchid\Screen\Actions\Button;
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
        return [
            'students' => UserSpecialty::filters()->withCount('subjects')->paginate()
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
            UserSpecialty::updateOrCreate(
                ['card_id' => $row['card_id']],
                $row
            );
        }

        Toast::success("Студентів імпортовано");
        return;
    }

    public function chooseStudent($studentId,$studentName)
    {
        Cookie::queue('user_specialty_id', $studentId, 1440);
        Toast::success("Вибрали $studentName");
        return redirect()->route('platform.selsubjects');
    }
}
