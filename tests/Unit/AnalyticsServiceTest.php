<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Group;
use App\Models\GroupSemesterLimit;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_calculates_masters_2026_cohort_statuses(): void
    {
        $group = Group::create([
            'name' => 'МАГ-26',
            'semester_count' => 3,
        ]);

        GroupSemesterLimit::create([
            'group_id' => $group->id,
            'semester' => 1,
            'max_subjects' => 2,
        ]);

        $subject1 = Subject::create(['name' => 'Дисципліна 1', 'active' => 1]);
        $subject2 = Subject::create(['name' => 'Дисципліна 2', 'active' => 1]);

        // Student 1: Fully chosen (2 / 2)
        $user1 = User::factory()->create();
        $st1 = UserSpecialty::create([
            'user_id' => $user1->id,
            'email' => 'm1@dspu.edu.ua',
            'card_id' => 'CARD-M-01',
            'full_name' => 'Магістр Повний',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет інформатики',
            'specialty' => '122 Комп’ютерні науки',
            'group_id' => $group->id,
            'group_name' => $group->name,
        ]);
        UserSpecialtySubject::create(['user_id' => $user1->id, 'user_specialty_id' => $st1->id, 'subject_id' => $subject1->id, 'semester' => 1]);
        UserSpecialtySubject::create(['user_id' => $user1->id, 'user_specialty_id' => $st1->id, 'subject_id' => $subject2->id, 'semester' => 1]);

        // Student 2: Partially chosen (1 / 2)
        $user2 = User::factory()->create();
        $st2 = UserSpecialty::create([
            'user_id' => $user2->id,
            'email' => 'm2@dspu.edu.ua',
            'card_id' => 'CARD-M-02',
            'full_name' => 'Магістр Частковий',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет інформатики',
            'specialty' => '122 Комп’ютерні науки',
            'group_id' => $group->id,
            'group_name' => $group->name,
        ]);
        UserSpecialtySubject::create(['user_id' => $user2->id, 'user_specialty_id' => $st2->id, 'subject_id' => $subject1->id, 'semester' => 1]);

        // Student 3: Not chosen (0 / 2)
        $user3 = User::factory()->create();
        $st3 = UserSpecialty::create([
            'user_id' => $user3->id,
            'email' => 'm3@dspu.edu.ua',
            'card_id' => 'CARD-M-03',
            'full_name' => 'Магістр Необравший',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Філологічний факультет',
            'specialty' => '014 Середня освіта',
            'group_id' => $group->id,
            'group_name' => $group->name,
        ]);

        // Student 4: Master 2025 (should NOT be included in 2026)
        $user4 = User::factory()->create();
        UserSpecialty::create([
            'user_id' => $user4->id,
            'email' => 'm4@dspu.edu.ua',
            'card_id' => 'CARD-M-04',
            'full_name' => 'Магістр Минулого Року',
            'study_start' => '2025-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет інформатики',
            'specialty' => '122 Комп’ютерні науки',
        ]);

        // Student 5: Bachelor 2026 (should NOT be included when degree is Master)
        $user5 = User::factory()->create();
        UserSpecialty::create([
            'user_id' => $user5->id,
            'email' => 'b1@dspu.edu.ua',
            'card_id' => 'CARD-B-01',
            'full_name' => 'Бакалавр 2026',
            'study_start' => '2026-09-01',
            'degree' => 'Бакалавр',
            'department' => 'Факультет інформатики',
            'specialty' => '122 Комп’ютерні науки',
        ]);

        $service = new AnalyticsService();
        $result = $service->getAnalyticsData(['entry_year' => '2026', 'degree' => 'Магістр']);

        $summary = $result['summary'];
        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(1, $summary['all']);
        $this->assertEquals(33.3, $summary['all_percent']);
        $this->assertEquals(1, $summary['partial']);
        $this->assertEquals(33.3, $summary['partial_percent']);
        $this->assertEquals(1, $summary['none']);
        $this->assertEquals(33.3, $summary['none_percent']);

        // Faculties breakdown
        $faculties = $result['faculties'];
        $this->assertCount(2, $faculties);

        $csFaculty = $faculties->firstWhere('name', 'Факультет інформатики');
        $this->assertNotNull($csFaculty);
        $this->assertEquals(2, $csFaculty['total']);
        $this->assertEquals(1, $csFaculty['all']);
        $this->assertEquals(1, $csFaculty['partial']);
        $this->assertEquals(0, $csFaculty['none']);

        $philFaculty = $faculties->firstWhere('name', 'Філологічний факультет');
        $this->assertNotNull($philFaculty);
        $this->assertEquals(1, $philFaculty['total']);
        $this->assertEquals(0, $philFaculty['all']);
        $this->assertEquals(0, $philFaculty['partial']);
        $this->assertEquals(1, $philFaculty['none']);

        // Specialties breakdown
        $specialties = $result['specialties'];
        $this->assertCount(2, $specialties);

        $csSpec = $specialties->firstWhere('name', '122 Комп’ютерні науки');
        $this->assertNotNull($csSpec);
        $this->assertEquals(2, $csSpec['total']);
        $this->assertEquals(1, $csSpec['all']);
        $this->assertEquals(1, $csSpec['partial']);
        $this->assertEquals(0, $csSpec['none']);
    }

    public function test_broad_specialty_splits_into_educational_programs(): void
    {
        $group = Group::create(['name' => 'СО-26', 'semester_count' => 3]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 2]);
        $sub = Subject::create(['name' => 'Педагогіка', 'active' => 1]);

        // Student A in Program 1 (Фізика, математика) - Fully chosen
        $u1 = User::factory()->create();
        $s1 = UserSpecialty::create([
            'user_id' => $u1->id,
            'email' => 'so1@dspu.edu.ua',
            'card_id' => 'CARD-SO-01',
            'full_name' => 'Студент ФізМат',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет фізики та математики',
            'specialty' => 'A4 Середня освіта',
            'education_program' => 'Середня освіта (Фізика, математика)',
            'group_id' => $group->id,
        ]);
        UserSpecialtySubject::create(['user_id' => $u1->id, 'user_specialty_id' => $s1->id, 'subject_id' => $sub->id, 'semester' => 1]);
        UserSpecialtySubject::create(['user_id' => $u1->id, 'user_specialty_id' => $s1->id, 'subject_id' => $sub->id, 'semester' => 1]);

        // Student B in Program 2 (Інформатика) - Not chosen
        $u2 = User::factory()->create();
        UserSpecialty::create([
            'user_id' => $u2->id,
            'email' => 'so2@dspu.edu.ua',
            'card_id' => 'CARD-SO-02',
            'full_name' => 'Студент Інформ',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет фізики та математики',
            'specialty' => 'A4 Середня освіта',
            'education_program' => 'Середня освіта (Інформатика)',
            'group_id' => $group->id,
        ]);

        $service = new AnalyticsService();
        $result = $service->getAnalyticsData(['entry_year' => '2026', 'degree' => 'Магістр']);

        $spec = $result['specialties']->firstWhere('name', 'A4 Середня освіта');
        $this->assertNotNull($spec);
        $this->assertTrue($spec['is_broad']);
        $this->assertEquals(2, $spec['total']);
        $this->assertCount(2, $spec['programs']);

        $progPhys = $spec['programs']->firstWhere('name', 'Середня освіта (Фізика, математика)');
        $this->assertNotNull($progPhys);
        $this->assertEquals(1, $progPhys['total']);
        $this->assertEquals(1, $progPhys['all']);
        $this->assertEquals(0, $progPhys['none']);

        $progInf = $spec['programs']->firstWhere('name', 'Середня освіта (Інформатика)');
        $this->assertNotNull($progInf);
        $this->assertEquals(1, $progInf['total']);
        $this->assertEquals(0, $progInf['all']);
        $this->assertEquals(1, $progInf['none']);
    }

    public function test_dekanat_user_is_scoped_to_department(): void
    {
        $dept = Department::create(['name' => 'Філологічний факультет', 'abbreviation' => 'ФФ']);

        $role = Role::create([
            'name' => 'Деканат',
            'slug' => 'dekanat',
            'permissions' => ['platform.systems.students' => 1],
        ]);

        $dekanatUser = User::factory()->create([
            'department_id' => $dept->id,
        ]);
        $dekanatUser->roles()->attach($role);

        // Student in CS Faculty
        UserSpecialty::create([
            'email' => 's1@dspu.edu.ua',
            'card_id' => 'CARD-01',
            'full_name' => 'Студент Інформатики',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет інформатики',
            'specialty' => '122 Комп’ютерні науки',
        ]);

        // Student in Philology Faculty
        UserSpecialty::create([
            'email' => 's2@dspu.edu.ua',
            'card_id' => 'CARD-02',
            'full_name' => 'Студент Філології',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Філологічний факультет',
            'specialty' => '014 Середня освіта',
        ]);

        $service = new AnalyticsService();
        $result = $service->getAnalyticsData(['entry_year' => '2026', 'degree' => 'Магістр'], $dekanatUser);

        $this->assertEquals(1, $result['summary']['total']);
        $this->assertCount(1, $result['faculties']);
        $this->assertEquals('Філологічний факультет', $result['faculties']->first()['name']);
    }
}
