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

            if (!empty($row['group_name'])) {
                $group =  Group::firstOrCreate(
                    ['name' => $row['group_name']], // Унікальна назва групи
                    [
                        'department_id' => $row['department_id'] ?? null,
                        'degree_id' => $row['degree_id'] ?? null, // 🔹 нове поле
                    ]
                );
                $row['group_id'] = $group->id;
            }

            // Залишаємо тільки дозволені колонки таблиці
            $row = array_intersect_key($row, array_flip($allowed));

            $userSpecialty = UserSpecialty::withTrashed()->firstWhere('card_id', $row['card_id']);

            $data = [
                'department_id' => $row['department_id'],
                'degree_id' => $row['degree_id'],
                'group_id' => $row['group_id'],
                'email' => $row['email'],
                'card_id' => $row['card_id'],
                'status_from' => $row['status_from'],
                'study_status' => $row['study_status'],
                'fo_id' => $row['fo_id'],
                'full_name' => $row['full_name'],
                'birth_date' => $row['birth_date'],
                'dpo_type' => $row['dpo_type'],
                'document_series' => $row['document_series'],
                'document_number' => $row['document_number'],
                'issue_date' => $row['issue_date'],
                'valid_until' => $row['valid_until'],
                'gender' => $row['gender'],
                'citizenship' => $row['citizenship'],
                'name_en' => $row['name_en'],
                'rnokpp' => $row['rnokpp'],
                'valid_rnokpp' => $row['valid_rnokpp'],
                'license_year' => $row['license_year'],
                'study_start' => $row['study_start'],
                'study_end' => $row['study_end'],
                'next_level_admission_date' => $row['next_level_admission_date'],
                'department' => $row['department'],
                'dual_form' => $row['dual_form'],
                'degree' => $row['degree'],
                'admission_basis' => $row['admission_basis'],
                'study_form' => $row['study_form'],
                'funding_source' => $row['funding_source'],
                'other_specialty' => $row['other_specialty'],
                'shortened_term' => $row['shortened_term'],
                'specialty' => $row['specialty'],
                'specialization' => $row['specialization'],
                'op_id' => $row['op_id'],
                'education_program' => $row['education_program'],
                'profession' => $row['profession'],
                'course' => $row['course'],
                'group_name' => $row['group_name'],
                'foreigner_type' => $row['foreigner_type'],
                'category_code' => $row['category_code'],
                'has_education_doc' => $row['has_education_doc'],
                'has_student_card' => $row['has_student_card'],
                'has_academic_reference' => $row['has_academic_reference'],
                'expulsion_reason' => $row['expulsion_reason'],
                'academic_leave_reason' => $row['academic_leave_reason'],
                'status_to' => $row['status_to'],
                'diploma_status' => $row['diploma_status'],
                'student_card_status' => $row['student_card_status'],
                'qualification_certificate_status' => $row['qualification_certificate_status'],
                'budget_year' => $row['budget_year'],
                'regional_order' => $row['regional_order'],
                'enrollment_order' => $row['enrollment_order'],
                'previous_institution' => $row['previous_institution'],
                'previous_education_doc' => $row['previous_education_doc'],
                'previous_study_info' => $row['previous_study_info'],
                'has_academic_reference_doc' => $row['has_academic_reference_doc'],
                'has_expulsion_reference' => $row['has_expulsion_reference'],
                'has_student_ticket' => $row['has_student_ticket'],
                'has_diploma' => $row['has_diploma'],
                'enrollment_info' => $row['enrollment_info'],
                'kb_entry' => $row['kb_entry'],
                'kr_without_pzso' => $row['kr_without_pzso'],
                'last_update' => $row['last_update'],
                'budget_transfer_category_code' => $row['budget_transfer_category_code'],
                'budget_transfer_category_name' => $row['budget_transfer_category_name'],
                'card_creation_method' => $row['card_creation_method'],
                'dissertation_defense_renewal' => $row['dissertation_defense_renewal'],
            ];
            if ($row['study_status'] == "Зараховано") {
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
