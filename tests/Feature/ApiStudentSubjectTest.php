<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiStudentSubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_students_subjects_api(): void
    {
        $response = $this->getJson('/api/students-subjects');
        $response->assertStatus(401);
    }

    public function test_student_without_permission_gets_forbidden(): void
    {
        $student = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($student)->getJson('/api/students-subjects');
        $response->assertStatus(403);
    }

    public function test_admin_can_retrieve_json_list_of_students_and_subjects(): void
    {
        $admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.students' => true,
            ],
        ]);

        $specialty = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => $admin->email,
            'card_id' => '13630000',
            'full_name' => 'Олександр Коваленко',
        ]);

        $subject = Subject::create([
            'name' => 'Штучний інтелект',
            'chair' => 'Кафедра системного аналізу',
            'active' => 1,
            'credits' => 4,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 2,
            'is_student_choice' => true,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/students-subjects');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'edebo',
                        'student_name',
                        'component',
                        'semester',
                    ],
                ],
                'total',
            ],
        ]);
    }

    public function test_admin_can_stream_csv_export(): void
    {
        $admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.students' => true,
            ],
        ]);

        $specialty = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => $admin->email,
            'card_id' => '13630001',
            'full_name' => 'Марія Шевченко',
        ]);

        $subject = Subject::create([
            'name' => 'Веб-дизайн',
            'chair' => 'Кафедра інформатики',
            'active' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        $response = $this->actingAs($admin)->get('/api/students-subjects?export=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
