<?php

namespace App\Orchid\Screens\Subject;

use App\Models\Subject;
use App\Orchid\Filters\EntryYearFilter;
use App\Orchid\Layouts\Subject\SubjectFiltersLayout;
use App\Orchid\Layouts\Subject\SubjectListLayout;
use App\Services\GoogleSheet\ReportSubjectsStudentsSheet;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $entryYear = request()->get('entry_year') ?? request()->input('filter.study_start');
        if (is_array($entryYear)) {
            $entryYear = reset($entryYear);
        }

        $subjectsQuery = Subject::filters()
            ->filtersApply([EntryYearFilter::class]);

        if (!empty($entryYear)) {
            $subjectsQuery->withCount([
                'userSpecialties as users_count' => function ($q) use ($entryYear) {
                    $q->where('study_start', 'like', $entryYear . '%');
                },
                'userSpecialties as total_users_count',
            ]);
        } else {
            $subjectsQuery->withCount('userSpecialties as users_count');
        }

        if ($user && $user->degree){
            $subjectsQuery->where('education_level', $user->degree->name);
        }

        $subjects = $subjectsQuery->paginate();

        $subjectIds = $subjects->getCollection()->pluck('id')->filter()->all();

        if (!empty($subjectIds)) {
            $countsByYear = DB::table('user_specialty_subjects')
                ->join('user_specialties', 'user_specialty_subjects.user_specialty_id', '=', 'user_specialties.id')
                ->whereIn('user_specialty_subjects.subject_id', $subjectIds)
                ->whereNull('user_specialties.deleted_at')
                ->selectRaw("user_specialty_subjects.subject_id, SUBSTR(user_specialties.study_start, 1, 4) as entry_year, count(*) as count")
                ->groupBy('user_specialty_subjects.subject_id', 'entry_year')
                ->orderByDesc('entry_year')
                ->get()
                ->groupBy('subject_id');

            $subjects->getCollection()->transform(function ($subject) use ($countsByYear) {
                $subject->year_counts = $countsByYear->get($subject->id, collect())
                    ->mapWithKeys(fn($row) => [($row->entry_year ?: '—') => (int)$row->count])
                    ->toArray();
                return $subject;
            });
        }

        return [
            'subjects' => $subjects,
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
                ->icon('bs.cloud-arrow-down')
                ->method('importFromGoogleSheet'),
            Button::make('Вигрузити звіт по дисциплінах')
                ->icon('bs.cloud-arrow-up')
                ->method('exportToGoogleSheet'),
            Link::make('Google Sheet')
                ->icon('bs.box-arrow-up-right')
                ->target('_blank')
                ->href(\App\Services\GoogleSheet\GoogleSheetService::getSheetUrl('subjects'))
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
            SubjectFiltersLayout::class,
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
            $query->select('user_specialties.id', 'user_specialties.full_name', 'user_specialties.specialty', 'user_specialties.group_name', 'user_specialties.study_form');
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
                            $student->group_name,
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
