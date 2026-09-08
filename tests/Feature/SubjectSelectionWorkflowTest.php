<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupSemesterLimit;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SubjectSelectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::updateOrCreate(['key' => 'subject_selection_enabled'], ['value' => '1']);
    }

    public function test_student_choosing_subject_sets_student_choice_true(): void
    {
        $user = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 2, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-111',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Андрій Шевченко',
            'specialty' => 'Комп\'ютерні науки',
        ]);

        $subject = Subject::create(['name' => 'Machine Learning', 'active' => 1]);

        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=2');

        $response->assertStatus(302);

        $this->assertDatabaseHas('user_specialty_subjects', [
            'user_id' => $user->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 2,
            'is_student_choice' => true,
        ]);
    }

    public function test_dekanat_choosing_subject_on_behalf_of_student_sets_student_choice_false(): void
    {
        $studentUser = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $dekanatUser = User::factory()->create(['permissions' => ['platform.index' => true, 'dekanat' => true]]);

        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $studentUser->id,
            'email' => $studentUser->email,
            'card_id' => 'CARD-222',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Олена Бондар',
            'specialty' => 'Інформатика',
        ]);

        $subject = Subject::create(['name' => 'Бази даних', 'active' => 1]);

        // Dekanat chooses on behalf of student
        $response = $this->actingAs($dekanatUser)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=1');

        $response->assertStatus(302);

        $this->assertDatabaseHas('user_specialty_subjects', [
            'user_id' => $dekanatUser->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => false,
        ]);
    }

    public function test_switching_semester_updates_existing_subject_record(): void
    {
        $user = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 2, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-333',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Максим Кривоніс',
        ]);

        $subject = Subject::create(['name' => 'Криптографія', 'active' => 1]);

        // 1. First pick for semester 1
        UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        // 2. Switch to semester 2
        $this->actingAs($user)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=2');

        $this->assertEquals(1, UserSpecialtySubject::where('user_specialty_id', $specialty->id)->where('subject_id', $subject->id)->count());
        $this->assertEquals(2, UserSpecialtySubject::where('user_specialty_id', $specialty->id)->where('subject_id', $subject->id)->first()->semester);
    }

    public function test_cancelling_subject_selection_deletes_record_and_creates_activity_log(): void
    {
        $user = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-444',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Юлія Тимошук',
            'specialty' => 'Кібербезпека',
        ]);

        $subject = Subject::create(['name' => 'Аналіз даних', 'active' => 1]);

        $choice = UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        // Cancel subject (semester = 0)
        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=0');

        $response->assertStatus(302);

        // Record should be deleted
        $this->assertDatabaseMissing('user_specialty_subjects', [
            'id' => $choice->id,
        ]);

        // Activity log should exist
        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'causer_type' => User::class,
        ]);
    }

    public function test_selection_blocked_when_campaign_is_disabled_for_students(): void
    {
        // Disable campaign
        Setting::updateOrCreate(['key' => 'subject_selection_enabled'], ['value' => '0']);

        $user = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-555',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Віктор Павлік',
        ]);

        $subject = Subject::create(['name' => 'Теорія алгоритмів', 'active' => 1]);

        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=1');

        $response->assertStatus(302);

        // Subject was NOT selected
        $this->assertDatabaseMissing('user_specialty_subjects', [
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_selection_allowed_when_campaign_is_disabled_for_admin(): void
    {
        // Disable campaign globally
        Setting::updateOrCreate(['key' => 'subject_selection_enabled'], ['value' => '0']);

        $adminUser = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
            ],
        ]);

        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $adminUser->id,
            'email' => $adminUser->email,
            'card_id' => 'CARD-666',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Адміністратор Системи',
        ]);

        $subject = Subject::create(['name' => 'Системне програмування', 'active' => 1]);

        $response = $this->actingAs($adminUser)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=1');

        $response->assertStatus(302);

        // Subject was successfully selected by admin despite closed campaign
        $this->assertDatabaseHas('user_specialty_subjects', [
            'user_specialty_id' => $specialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
        ]);
    }

    public function test_new_selection_blocked_for_inactive_subject(): void
    {
        $user = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-777',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Олена Петренко',
        ]);

        $inactiveSubject = Subject::create(['name' => 'Минулорічна неактивна дисципліна', 'active' => 0]);

        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $specialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $inactiveSubject->id . '&subjectName=' . urlencode($inactiveSubject->name) . '&semester=1');

        $response->assertStatus(302);

        $this->assertDatabaseMissing('user_specialty_subjects', [
            'user_specialty_id' => $specialty->id,
            'subject_id' => $inactiveSubject->id,
        ]);
    }

    public function test_previous_selection_preserved_and_visible_for_inactive_subject(): void
    {
        $user = User::factory()->create(['permissions' => ['platform.index' => true]]);
        $group = Group::create(['name' => 'КН-31', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-888',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Богдан Коваль',
        ]);

        $inactiveSubject = Subject::create(['name' => 'Історія педагогіки (архів)', 'active' => 0]);

        // Previously selected record exists in database
        UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $specialty->id,
            'subject_id' => $inactiveSubject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $specialty->id)
            ->get(route('platform.selsubjects'));

        $response->assertOk();
        $response->assertSee('Історія педагогіки (архів)');
        $response->assertSee('Архівна');
    }
}
