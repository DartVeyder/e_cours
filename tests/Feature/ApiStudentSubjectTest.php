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

    public function test_api_returns_json_list_of_students_and_subjects(): void
    {
        $user = User::factory()->create();

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
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
            'user_id' => $user->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 2,
            'is_student_choice' => true,
        ]);

        $response = $this->getJson('/api/students-subjects');

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

    public function test_api_can_stream_csv_export(): void
    {
        $user = User::factory()->create();

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => '13630001',
            'full_name' => 'Марія Шевченко',
        ]);

        $subject = Subject::create([
            'name' => 'Веб-дизайн',
            'chair' => 'Кафедра інформатики',
            'active' => 1,
        ]);

        UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        $response = $this->get('/api/students-subjects?export=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
