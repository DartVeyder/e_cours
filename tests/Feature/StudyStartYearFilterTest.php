<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use App\Services\GroupExcelExport;
use App\Services\StudentsExcelExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyStartYearFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_entry_years_options_returns_distinct_sorted_years(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        UserSpecialty::create([
            'user_id' => $user1->id,
            'email' => $user1->email,
            'card_id' => 'CARD-2025',
            'full_name' => 'Студент 2025',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        UserSpecialty::create([
            'user_id' => $user2->id,
            'email' => $user2->email,
            'card_id' => 'CARD-2026',
            'full_name' => 'Студент 2026',
            'study_start' => '2026-09-08',
            'specialty' => '121 ІПЗ',
        ]);

        UserSpecialty::create([
            'user_id' => $user3->id,
            'email' => $user3->email,
            'card_id' => 'CARD-2024',
            'full_name' => 'Студент 2024',
            'study_start' => '2024-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        $options = UserSpecialty::getEntryYearsOptions();

        $this->assertEquals(['2026' => '2026', '2025' => '2025', '2024' => '2024'], $options);
    }

    public function test_entry_year_accessor_returns_four_digit_year(): void
    {
        $user = User::factory()->create();
        $spec = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-ACC',
            'full_name' => 'Студент Аксесор',
            'study_start' => '2025-09-15',
            'specialty' => '121 ІПЗ',
        ]);

        $this->assertEquals('2025', $spec->entry_year);
    }

    public function test_subject_specialty_list_screen_displays_entry_year_column_and_badges(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $subject = Subject::create([
            'name' => 'Штучний інтелект',
            'active' => 1,
        ]);

        $spec2025 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'student2025@dspu.edu.ua',
            'card_id' => 'CARD-S2025',
            'full_name' => 'Іваненко Петро',
            'group_name' => 'ІПЗ-25',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
            'study_form' => 'Денна',
        ]);

        $spec2026 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'student2026@dspu.edu.ua',
            'card_id' => 'CARD-S2026',
            'full_name' => 'Сидоренко Анна',
            'group_name' => 'ІПЗ-26',
            'study_start' => '2026-09-01',
            'specialty' => '121 ІПЗ',
            'study_form' => 'Денна',
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2025->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2026->id,
            'subject_id' => $subject->id,
            'semester' => 2,
            'is_student_choice' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('platform.subjects.specialty', $subject->id));

        $response->assertOk();
        $response->assertSee('Рік вступу');
        $response->assertSee('2025');
        $response->assertSee('2026');
        $response->assertSee('Іваненко Петро');
        $response->assertSee('Сидоренко Анна');
        $response->assertSee('Експорт в Excel');
    }

    public function test_subject_specialty_list_screen_filters_by_entry_year(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $subject = Subject::create([
            'name' => 'Кібербезпека',
            'active' => 1,
        ]);

        $spec2025 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'spec2025@dspu.edu.ua',
            'card_id' => 'CARD-F2025',
            'full_name' => 'Студент Двадцять Пять',
            'group_name' => 'ІПЗ-25',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
            'study_form' => 'Денна',
        ]);

        $spec2026 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'spec2026@dspu.edu.ua',
            'card_id' => 'CARD-F2026',
            'full_name' => 'Студент Двадцять Шість',
            'group_name' => 'ІПЗ-26',
            'study_start' => '2026-09-01',
            'specialty' => '121 ІПЗ',
            'study_form' => 'Денна',
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2025->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2026->id,
            'subject_id' => $subject->id,
            'semester' => 2,
            'is_student_choice' => 1,
        ]);

        // Filter by 2025
        $response2025 = $this->actingAs($admin)->get(route('platform.subjects.specialty', [
            'subject' => $subject->id,
            'filter' => ['study_start' => '2025'],
        ]));

        $response2025->assertOk();
        $response2025->assertSee('Студент Двадцять Пять');
        $response2025->assertDontSee('Студент Двадцять Шість');

        // Filter by 2026
        $response2026 = $this->actingAs($admin)->get(route('platform.subjects.specialty', [
            'subject' => $subject->id,
            'filter' => ['study_start' => '2026'],
        ]));

        $response2026->assertOk();
        $response2026->assertSee('Студент Двадцять Шість');
        $response2026->assertDontSee('Студент Двадцять Пять');
    }

    public function test_student_list_screen_displays_entry_year(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'test_stud@dspu.edu.ua',
            'card_id' => 'CARD-STUD',
            'full_name' => 'Коваленко Олег',
            'group_name' => 'ІПЗ-21',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        $response = $this->actingAs($admin)->get(route('platform.students'));

        $response->assertOk();
        $response->assertSee('Рік вступу');
        $response->assertSee('2025');
        $response->assertSee('Коваленко Олег');
    }

    public function test_student_list_screen_filters_by_column_and_top_filter(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'stud25@dspu.edu.ua',
            'card_id' => 'CARD-2025',
            'full_name' => 'Студент 2025',
            'group_name' => 'ІПЗ-25',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'stud26@dspu.edu.ua',
            'card_id' => 'CARD-2026',
            'full_name' => 'Студент 2026',
            'group_name' => 'ІПЗ-26',
            'study_start' => '2026-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        // Filter via table column header filter (filter[study_start])
        $responseColumn = $this->actingAs($admin)->get(route('platform.students', [
            'filter' => ['study_start' => ['2025']],
        ]));
        $responseColumn->assertOk();
        $responseColumn->assertSee('Студент 2025');
        $responseColumn->assertDontSee('Студент 2026');

        // Filter via top selection dropdown filter (entry_year)
        $responseTop = $this->actingAs($admin)->get(route('platform.students', [
            'entry_year' => '2026',
        ]));
        $responseTop->assertOk();
        $responseTop->assertSee('Студент 2026');
        $responseTop->assertDontSee('Студент 2025');
    }

    public function test_students_group_screen_displays_entry_year(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $group = Group::create(['name' => 'ІПЗ-21']);

        UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'group_stud@dspu.edu.ua',
            'card_id' => 'CARD-GROUP-1',
            'full_name' => 'Груповий Студент',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group->id,
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        $response = $this->actingAs($admin)->get(route('platform.students.group', ['group' => 'ІПЗ-21']));

        $response->assertOk();
        $response->assertSee('Рік вступу');
        $response->assertSee('2025');
        $response->assertSee('Груповий Студент');
    }

    public function test_excel_exports_include_entry_year_stream(): void
    {
        $user = User::factory()->create();
        $group = Group::create(['name' => 'ІПЗ-25']);

        $spec = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-EXCEL',
            'full_name' => 'Експорт Студент',
            'group_name' => 'ІПЗ-25',
            'group_id' => $group->id,
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
            'study_form' => 'Денна',
        ]);

        $subject = Subject::create([
            'name' => 'Веб-дизайн',
            'active' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $spec->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        // Students Excel Export
        $studentsExport = (new StudentsExcelExport())->export(collect([$spec]));
        $this->assertEquals(200, $studentsExport->getStatusCode());
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $studentsExport->headers->get('Content-Type'));

        // Group Excel Export
        $groupExport = (new GroupExcelExport())->export('ІПЗ-25');
        $this->assertEquals(200, $groupExport->getStatusCode());
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $groupExport->headers->get('Content-Type'));
    }

    public function test_subject_list_screen_displays_total_and_entry_year_badges(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $subjectWithStudents = Subject::create([
            'name' => 'Розподілені системи',
            'active' => 1,
        ]);

        $subjectEmpty = Subject::create([
            'name' => 'Порожня дисципліна',
            'active' => 1,
        ]);

        $spec2025 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'sub_s2025@dspu.edu.ua',
            'card_id' => 'CARD-SUB-25',
            'full_name' => 'Студент Розподіл 2025',
            'group_name' => 'ІПЗ-25',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        $spec2026_1 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'sub_s2026_1@dspu.edu.ua',
            'card_id' => 'CARD-SUB-26-1',
            'full_name' => 'Студент Розподіл 2026 Перший',
            'group_name' => 'ІПЗ-26',
            'study_start' => '2026-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        $spec2026_2 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'sub_s2026_2@dspu.edu.ua',
            'card_id' => 'CARD-SUB-26-2',
            'full_name' => 'Студент Розподіл 2026 Другий',
            'group_name' => 'ІПЗ-26',
            'study_start' => '2026-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2025->id,
            'subject_id' => $subjectWithStudents->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2026_1->id,
            'subject_id' => $subjectWithStudents->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2026_2->id,
            'subject_id' => $subjectWithStudents->id,
            'semester' => 2,
            'is_student_choice' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('platform.subjects'));

        $response->assertOk();
        $response->assertSee('Кількість вибрало');
        $response->assertSee('Розподілені системи');
        $response->assertSee('Всього:');
        $response->assertSee('2026: 2');
        $response->assertSee('2025: 1');
        $response->assertSee('entry_year=2026');
        $response->assertSee('entry_year=2025');
        $response->assertSee('Порожня дисципліна');
    }

    public function test_subject_list_screen_filters_by_entry_year(): void
    {
        $admin = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $subject2025Only = Subject::create([
            'name' => 'Курс тільки для 2025',
            'active' => 1,
        ]);

        $subject2026Only = Subject::create([
            'name' => 'Курс тільки для 2026',
            'active' => 1,
        ]);

        $spec2025 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'filter_2025@dspu.edu.ua',
            'card_id' => 'CARD-FLT-25',
            'full_name' => 'Студент 2025 Фільтр',
            'study_start' => '2025-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        $spec2026 = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => 'filter_2026@dspu.edu.ua',
            'card_id' => 'CARD-FLT-26',
            'full_name' => 'Студент 2026 Фільтр',
            'study_start' => '2026-09-01',
            'specialty' => '121 ІПЗ',
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2025->id,
            'subject_id' => $subject2025Only->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $spec2026->id,
            'subject_id' => $subject2026Only->id,
            'semester' => 1,
            'is_student_choice' => 1,
        ]);

        // Filter by 2026
        $response2026 = $this->actingAs($admin)->get(route('platform.subjects', [
            'entry_year' => '2026',
        ]));

        $response2026->assertOk();
        $response2026->assertSee('Курс тільки для 2026');
        $response2026->assertDontSee('Курс тільки для 2025');

        // Filter by 2025
        $response2025 = $this->actingAs($admin)->get(route('platform.subjects', [
            'entry_year' => '2025',
        ]));

        $response2025->assertOk();
        $response2025->assertSee('Курс тільки для 2025');
        $response2025->assertDontSee('Курс тільки для 2026');
    }
}
