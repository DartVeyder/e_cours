<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class GroupExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_can_export_group_to_excel(): void
    {
        $admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.groups' => true,
            ],
        ]);

        $group = Group::create(['name' => 'ПІ-41', 'semester_count' => 8]);

        $student = UserSpecialty::create([
            'user_id' => $admin->id,
            'email' => $admin->email,
            'card_id' => 'CARD-EXPORT-1',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Тарас Шевченко',
        ]);

        $subject = Subject::create(['name' => 'Мобільна розробка', 'active' => 1]);

        UserSpecialtySubject::create([
            'user_id' => $admin->id,
            'user_specialty_id' => $student->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        $response = $this->actingAs($admin)->get('/export/group/' . urlencode($group->name) . '/excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $admin->id,
        ]);
    }

    public function test_student_without_permission_cannot_export_group_excel(): void
    {
        $student = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $group = Group::create(['name' => 'ПІ-41', 'semester_count' => 8]);

        $response = $this->actingAs($student)->get('/export/group/' . urlencode($group->name) . '/excel');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_export_group_and_is_redirected_to_login(): void
    {
        $response = $this->get('/export/group/ПІ-41/excel');
        $response->assertRedirect('/login');
    }

    public function test_dekanat_can_export_own_department_group(): void
    {
        $dept = Department::create(['name' => 'Фізико-математичний факультет', 'abbreviation' => 'ФМФ']);

        $role = Role::firstOrCreate(['slug' => 'dekanat'], [
            'name' => 'Деканат',
            'permissions' => ['platform.index' => 1, 'platform.systems.groups' => 1],
        ]);

        $dekanatUser = User::factory()->create([
            'department_id' => $dept->id,
        ]);
        $dekanatUser->roles()->attach($role);

        $group = Group::create([
            'name' => 'Ф-11',
            'department_id' => $dept->id,
            'semester_count' => 8,
        ]);

        $response = $this->actingAs($dekanatUser)->get('/export/group/' . urlencode($group->name) . '/excel');
        $response->assertStatus(200);
    }

    public function test_dekanat_cannot_export_group_of_another_department(): void
    {
        $deptMath = Department::create(['name' => 'Фізико-математичний факультет', 'abbreviation' => 'ФМФ']);
        $deptHistory = Department::create(['name' => 'Історичний факультет', 'abbreviation' => 'ІФ']);

        $role = Role::firstOrCreate(['slug' => 'dekanat'], [
            'name' => 'Деканат',
            'permissions' => ['platform.index' => 1, 'platform.systems.groups' => 1],
        ]);

        $dekanatUser = User::factory()->create([
            'department_id' => $deptMath->id,
        ]);
        $dekanatUser->roles()->attach($role);

        $groupHistory = Group::create([
            'name' => 'ІСТ-11',
            'department_id' => $deptHistory->id,
            'semester_count' => 8,
        ]);

        $response = $this->actingAs($dekanatUser)->get('/export/group/' . urlencode($groupHistory->name) . '/excel');
        $response->assertStatus(403);
    }
}
