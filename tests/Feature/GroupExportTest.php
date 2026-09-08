<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_export_group_to_excel(): void
    {
        $user = User::factory()->create();

        $group = Group::create(['name' => 'ПІ-41', 'semester_count' => 8]);

        $student = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-EXPORT-1',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Тарас Шевченко',
        ]);

        $subject = Subject::create(['name' => 'Мобільна розробка', 'active' => 1]);

        UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $student->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        $response = $this->actingAs($user)->get('/export/group/' . urlencode($group->name) . '/excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Verify activity log was recorded
        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_export_group_and_is_redirected_to_login(): void
    {
        $response = $this->get('/export/group/ПІ-41/excel');
        $response->assertRedirect('/login');
    }
}
