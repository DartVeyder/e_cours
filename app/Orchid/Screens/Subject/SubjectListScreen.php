<?php

namespace App\Orchid\Screens\Subject;

use App\Models\Subject;
use App\Orchid\Layouts\Subject\SubjectListLayout;
use App\Services\GoogleSheet\ReportSubjectsStudentsSheet;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class SubjectListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = Auth::user()->load(['department','degree', 'roles']);

        $subjectsQuery = Subject::filters()->withCount('users');

        if ($user && $user->degree){
            $subjectsQuery->where('education_level', $user->degree->name);
        }

        return [
            'subjects' =>  $subjectsQuery->paginate()

        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список предметів';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Загрузити дисципліни')
                ->method('importFromGoogleSheet'),
            Button::make('Вигрузити звіт по дисциплінах')
                ->method('exportToGoogleSheet'),
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
            SubjectListLayout::class,
        ];
    }

    public function importFromGoogleSheet()
    {
        $selsubjectSheet = new SelsubjectSheet();
        $errors = [];
        $imported = 0;
        $created = 0;
        $updated = 0;

        $degrees = \App\Models\Degree::all()->mapWithKeys(function ($degree) {
            return [mb_strtolower($degree->name) => $degree->id];
        })->toArray();

        foreach ($selsubjectSheet->readAssoc() as $index => $row) {
            try {
                // конвертація "так/ні" у 1/0 для поля active
                if (isset($row['active'])) {
                    $value = mb_strtolower(trim($row['active']));
                    if ($value === 'так') {
                        $row['active'] = 1;
                    } elseif ($value === 'ні') {
                        $row['active'] = 0;
                    } elseif (in_array($value, ['1', '0'], true)) {
                        $row['active'] = (int) $value; // лишаємо як є
                    } else {
                        $row['active'] = 0; // дефолтне значення
                    }
                }

                if (!empty($row['education_level'])) {
                    $row['degree_id'] = $degrees[$row['education_level']] ?? null;
                }


                if (!empty($row['code'])) {
                    $subject = Subject::updateOrCreate(
                        ['code' => $row['code']], // перевірка унікальності по code
                        $row
                    );
                } else {
                    // Можна логувати або пропустити
                    Log::warning('Пропущено запис без коду', $row);
                    $errors[] = "Пропущено запис без коду $row[name] ";
                }

                $imported++;
                if ($subject->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }

            } catch (\Throwable $e) {
                $errors[] = "Рядок " . ($index + 1) .
                    " (код: " . ($row['code'] ?? '—') .
                    "): " . $e->getMessage();
            }
        }

        if ($errors) {
            $message = "Імпорт завершено з помилками (" . count($errors) . "):\n" .
                implode("\n", $errors);

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'imported' => $imported,
                    'created'  => $created,
                    'updated'  => $updated,
                    'errors'   => $errors,
                ])
                ->log('Імпорт дисциплін завершено з помилками');

            Toast::error($message);

        } else {
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'imported' => $imported,
                    'created'  => $created,
                    'updated'  => $updated,
                ])
                ->log("Дисципліни імпортовано: нових $created, оновлено $updated");

            Toast::success("Дисципліни імпортовано: нових $created, оновлено $updated");
        }

        return;
    }



    public function exportToGoogleSheet(){
        $reportSubjectsStudentsSheet = new ReportSubjectsStudentsSheet();

        $subjects = Subject::with(['userSpecialties' => function($query) {
            $query->select('user_specialties.id', 'user_specialties.full_name', 'user_specialties.specialty', 'user_specialties.group', 'user_specialties.study_form');
        }])
            ->whereHas('userSpecialties')
            ->get()
            ->groupBy('department');

        foreach ($subjects as $department => $items) {
            $data = [];

            foreach ($items as $subject) {
                // Групуємо студентів предмета по формі навчання
                $groupedStudents = $subject->userSpecialties->groupBy('study_form');

                foreach ($groupedStudents as $form => $students) {
                    // Додаємо заголовок предмета з формою навчання
                    $data[] = [$subject->name . " ({$form})"];

                    // Додаємо заголовок таблиці студентів
                    $data[] = ['Повне ім’я', 'Спеціальність', 'Група'];

                    // Додаємо студентів
                    foreach ($students as $student) {
                        $data[] = [
                            $student->full_name,
                            $student->specialty,
                            $student->group,
                        ];
                    }

                    $data[] = [''];
                    $data[] = [''];
                }
            }

            $newSheetId = $reportSubjectsStudentsSheet->createSheet($department);
            $reportSubjectsStudentsSheet->writeBySheetId($newSheetId, $data);
        }


//        dd($subjects);
//        foreach ($subjects as $subject) {
//            $newSheetId = $reportSubjectsStudentsSheet->createSheet($subject->name);
//            $data = $subject->userSpecialties->makeHidden('pivot')->map(fn($item) => array_values($item->toArray()))->toArray();
//            array_unshift($data, ['Повне ім’я', 'Спеціальність', 'Група']);
//
//            $reportSubjectsStudentsSheet->writeBySheetId($newSheetId, $data);
//        }

        Toast::success("Звіт загружено в гугл таблицю");
        return;
    }

}
