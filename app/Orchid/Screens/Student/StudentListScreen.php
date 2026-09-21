<?php

namespace App\Orchid\Screens\Student;

use App\Models\Group;
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


        $user = Auth::user()->load(['department', 'degree', 'roles']);

        $specialtiesQuery = UserSpecialty::filters()
            ->filtersApply([\App\Orchid\Filters\EntryYearFilter::class, \App\Orchid\Filters\SubjectSelectionFilter::class])
            ->with(['group.semesterLimits'])
            ->withCount('subjects');

        if ($user && $user->department && $user->roles->contains('slug', 'dekanat')) {
            $specialtiesQuery->where('department', $user->department->name);
        }

        if ($user && $user->degree) {
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

    public function permission(): ?iterable
    {
        return [
            'platform.systems.students',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $exportParams = [];
        if (request()->has('filter')) {
            foreach (request()->get('filter') as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $exportParams["filter[$key][$k]"] = $v;
                    }
                } else {
                    $exportParams["filter[$key]"] = $value;
                }
            }
        }
        if (request()->has('sort')) {
            $exportParams['sort'] = request()->get('sort');
        }
        if (request()->has('entry_year')) {
            $exportParams['entry_year'] = request()->get('entry_year');
        }
        if (request()->has('subject_selection')) {
            $exportParams['subject_selection'] = request()->get('subject_selection');
        }

        return [
            Button::make('Експорт в Excel')
                ->icon('cloud-download')
                ->method('export', $exportParams)
                ->rawClick(),
            Button::make('Загрузити студентів')
                ->icon('bs.cloud-arrow-down')
                ->method('importStudentsFromGoogleSheet'),
            Link::make('Google Sheet')
                ->icon('bs.box-arrow-up-right')
                ->target('_blank')
                ->href(\App\Services\GoogleSheet\GoogleSheetService::getSheetUrl('students'))
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
            \App\Orchid\Layouts\Student\StudentFiltersLayout::class,
            StudentListLayout::class,
        ];
    }

    public function importStudentsFromGoogleSheet()
    {
        $studentsSheet = new StudentsSheet();

        foreach ($studentsSheet->readAssoc() as $row) {
            if (empty($row['card_id'])) {
                continue;
            }

            // Отримуємо список колонок таблиці user_specialties
            $allowed = Schema::getColumnListing('user_specialties');

            // Визначаємо degree_id, якщо в рядку є назва рівня освіти
            $row['degree_id'] = null;
            if (!empty($row['degree'])) {
                $degree = \App\Models\Degree::where('name', $row['degree'])->first();
                $row['degree_id'] = $degree ? $degree->id : null;
            }

            // Визначаємо department_id, якщо в рядку є назва департаменту
            $row['department_id'] = null;
            if (!empty($row['department'])) {
                $department = \App\Models\Department::where('name', $row['department'])->first();
                $row['department_id'] = $department ? $department->id : null;
            }

            $groupName = trim($row['group_name'] ?? '');
            $row['group_id'] = null;
            if ($groupName !== '' && $groupName !== '?' && $groupName !== '-') {
                $group = Group::firstOrCreate(
                    ['name' => $groupName], // Унікальна назва групи
                    [
                        'department_id' => $row['department_id'] ?? null,
                        'degree_id' => $row['degree_id'] ?? null, // 🔹 нове поле
                    ]
                );
                $row['group_id'] = $group->id;
                $row['group_name'] = $groupName;
            } else {
                $row['group_name'] = null;
            }

            // Залишаємо тільки дозволені колонки таблиці
            $row = array_intersect_key($row, array_flip($allowed));

            $userSpecialty = UserSpecialty::withTrashed()->firstWhere('card_id', $row['card_id']);

            $data = [
                'department_id' => $row['department_id'] ?? null,
                'degree_id' => $row['degree_id'] ?? null,
                'group_id' => $row['group_id'] ?? null,
                'email' => $row['email'] ?? null,
                'card_id' => $row['card_id'] ?? null,
                'status_from' => $row['status_from'] ?? null,
                'study_status' => $row['study_status'] ?? null,
                'fo_id' => $row['fo_id'] ?? null,
                'full_name' => $row['full_name'] ?? null,
                'birth_date' => $row['birth_date'] ?? null,
                'dpo_type' => $row['dpo_type'] ?? null,
                'document_series' => $row['document_series'] ?? null,
                'document_number' => $row['document_number'] ?? null,
                'issue_date' => $row['issue_date'] ?? null,
                'valid_until' => $row['valid_until'] ?? null,
                'gender' => $row['gender'] ?? null,
                'citizenship' => $row['citizenship'] ?? null,
                'name_en' => $row['name_en'] ?? null,
                'rnokpp' => $row['rnokpp'] ?? null,
                'valid_rnokpp' => $row['valid_rnokpp'] ?? null,
                'license_year' => $row['license_year'] ?? null,
                'study_start' => $row['study_start'] ?? null,
                'study_end' => $row['study_end'] ?? null,
                'next_level_admission_date' => $row['next_level_admission_date'] ?? null,
                'department' => $row['department'] ?? null,
                'dual_form' => $row['dual_form'] ?? null,
                'degree' => $row['degree'] ?? null,
                'admission_basis' => $row['admission_basis'] ?? null,
                'study_form' => $row['study_form'] ?? null,
                'funding_source' => $row['funding_source'] ?? null,
                'other_specialty' => $row['other_specialty'] ?? null,
                'shortened_term' => $row['shortened_term'] ?? null,
                'specialty' => $row['specialty'] ?? null,
                'specialization' => $row['specialization'] ?? null,
                'op_id' => $row['op_id'] ?? null,
                'education_program' => $row['education_program'] ?? null,
                'profession' => $row['profession'] ?? null,
                'course' => $row['course'] ?? null,
                'group_name' => $row['group_name'] ?? null,
                'foreigner_type' => $row['foreigner_type'] ?? null,
                'category_code' => $row['category_code'] ?? null,
                'has_education_doc' => $row['has_education_doc'] ?? null,
                'has_student_card' => $row['has_student_card'] ?? null,
                'has_academic_reference' => $row['has_academic_reference'] ?? null,
                'expulsion_reason' => $row['expulsion_reason'] ?? null,
                'academic_leave_reason' => $row['academic_leave_reason'] ?? null,
                'status_to' => $row['status_to'] ?? null,
                'diploma_status' => $row['diploma_status'] ?? null,
                'student_card_status' => $row['student_card_status'] ?? null,
                'qualification_certificate_status' => $row['qualification_certificate_status'] ?? null,
                'budget_year' => $row['budget_year'] ?? null,
                'regional_order' => $row['regional_order'] ?? null,
                'enrollment_order' => $row['enrollment_order'] ?? null,
                'previous_institution' => $row['previous_institution'] ?? null,
                'previous_education_doc' => $row['previous_education_doc'] ?? null,
                'previous_study_info' => $row['previous_study_info'] ?? null,
                'has_academic_reference_doc' => $row['has_academic_reference_doc'] ?? null,
                'has_expulsion_reference' => $row['has_expulsion_reference'] ?? null,
                'has_student_ticket' => $row['has_student_ticket'] ?? null,
                'has_diploma' => $row['has_diploma'] ?? null,
                'enrollment_info' => $row['enrollment_info'] ?? null,
                'kb_entry' => $row['kb_entry'] ?? null,
                'kr_without_pzso' => $row['kr_without_pzso'] ?? null,
                'last_update' => $row['last_update'] ?? null,
                'budget_transfer_category_code' => $row['budget_transfer_category_code'] ?? null,
                'budget_transfer_category_name' => $row['budget_transfer_category_name'] ?? null,
                'card_creation_method' => $row['card_creation_method'] ?? null,
                'dissertation_defense_renewal' => $row['dissertation_defense_renewal'] ?? null,
            ];
            if ($row['study_status'] == "Зараховано" || $row['study_status'] == "Змінено фінансування") {
                if ($userSpecialty) {
                    // Оновлюємо існуючий запис і відновлюємо, якщо був soft-deleted
                    $userSpecialty->update($data);
                    if ($userSpecialty->trashed()) {
                        $userSpecialty->restore();
                    }
                } else {
                    // Створюємо новий запис
                    UserSpecialty::create($data);
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


    public function chooseStudent($studentId, $studentName)
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

    public function export()
    {
        $user = Auth::user()->load(['department', 'degree', 'roles']);

        $specialtiesQuery = UserSpecialty::filters()
            ->filtersApply([\App\Orchid\Filters\EntryYearFilter::class, \App\Orchid\Filters\SubjectSelectionFilter::class])
            ->with(['group.semesterLimits'])
            ->withCount('subjects');

        if ($user && $user->department && $user->roles->contains('slug', 'dekanat')) {
            $specialtiesQuery->where('department', $user->department->name);
        }

        if ($user && $user->degree) {
            $specialtiesQuery->where('degree', $user->degree->name);
        }

        $students = $specialtiesQuery->get();

        activity()
            ->causedBy(Auth::user())
            ->log("Експорт студентів у Excel");

        return (new \App\Services\StudentsExcelExport())->export($students);
    }
}
