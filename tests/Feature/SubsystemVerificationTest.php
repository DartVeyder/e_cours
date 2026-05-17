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
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SubsystemVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable elective selection in settings by default for tests
        Setting::create([
            'key' => 'subject_selection_enabled',
            'value' => '1',
        ]);
    }

    /**
     * Scenario 1: Successful selection of an educational component by a student.
     */
    public function test_student_can_successfully_select_elective_course(): void
    {
        // 1. Create a user
        $user = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        // 2. Create student group with semester limits
        $group = Group::create([
            'name' => 'IT-41',
            'semester_count' => 8,
        ]);

        GroupSemesterLimit::create([
            'group_id' => $group->id,
            'semester' => 1,
            'max_subjects' => 2,
        ]);

        // 3. Create a student specialty for this user
        $userSpecialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'EDEBO-123456',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Іван Петренко',
        ]);

        // 4. Create a selective subject
        $subject = Subject::create([
            'name' => 'Хмарні технології',
            'chair' => 'Кафедра інформатики',
            'active' => 1,
        ]);

        // 5. Authenticate user, set cookie, and perform selection request
        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $userSpecialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=1');

        // Orchid action redirects back
        $response->assertStatus(302);

        // Verify that the record was successfully created in the associative table
        $this->assertDatabaseHas('user_specialty_subjects', [
            'user_id' => $user->id,
            'user_specialty_id' => $userSpecialty->id,
            'subject_id' => $subject->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);
    }

    /**
     * Scenario 2: Quota limit verification where the number of selected disciplines
     * reaches or exceeds the maximum limit set by the administrator.
     */
    public function test_subject_selection_is_blocked_when_quota_limit_is_exceeded(): void
    {
        // 1. Create a user
        $user = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        // 2. Create student group with 1 max subject limit for semester 1
        $group = Group::create([
            'name' => 'IT-41',
            'semester_count' => 8,
        ]);

        GroupSemesterLimit::create([
            'group_id' => $group->id,
            'semester' => 1,
            'max_subjects' => 1, // Quota limit is 1 subject
        ]);

        // 3. Create a student specialty for this user
        $userSpecialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'EDEBO-123456',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'full_name' => 'Іван Петренко',
        ]);

        // 4. Create two selective subjects
        $subject1 = Subject::create([
            'name' => 'Хмарні технології',
            'chair' => 'Кафедра інформатики',
            'active' => 1,
        ]);

        $subject2 = Subject::create([
            'name' => 'Паралельні обчислення',
            'chair' => 'Кафедра інформатики',
            'active' => 1,
        ]);

        // 5. Pre-fill quota by selecting the first subject
        UserSpecialtySubject::create([
            'user_id' => $user->id,
            'user_specialty_id' => $userSpecialty->id,
            'subject_id' => $subject1->id,
            'semester' => 1,
            'is_student_choice' => true,
        ]);

        // 6. Attempt to select a second subject (which exceeds the quota of 1)
        $response = $this->actingAs($user)
            ->withCookie('user_specialty_id', $userSpecialty->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject2->id . '&subjectName=' . urlencode($subject2->name) . '&semester=1');

        // Response should still return 302 (redirect back in Orchid style)
        $response->assertStatus(302);

        // Verify that the second subject choice is blocked and database remains unchanged
        $this->assertDatabaseMissing('user_specialty_subjects', [
            'user_specialty_id' => $userSpecialty->id,
            'subject_id' => $subject2->id,
        ]);

        // Count of selected subjects for semester 1 remains 1
        $this->assertEquals(1, UserSpecialtySubject::where('user_specialty_id', $userSpecialty->id)->where('semester', 1)->count());
    }

    /**
     * Scenario 3: Security check when logging in with a non-university email account.
     */
    public function test_auth_blocks_users_from_external_email_domains(): void
    {
        // 1. Mock Socialite Google provider to return a non-university email
        $nonUniversityEmail = 'stranger.danger@gmail.com';
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        
        $googleUser->shouldReceive('getId')->andReturn('google-id-99999');
        $googleUser->shouldReceive('getEmail')->andReturn($nonUniversityEmail);
        $googleUser->shouldReceive('getName')->andReturn('Stranger Danger');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock('Laravel\Socialite\Two\AbstractProvider')
                ->shouldReceive('user')
                ->andReturn($googleUser)
                ->getMock()
            );

        // 2. Perform callback request
        $response = $this->get('/auth/google/callback');

        // Verify it redirects back to login page
        $response->assertRedirect('/login');

        // Verify that authentication has been blocked
        $this->assertFalse(Auth::check());

        // Verify informative error message is present in the session
        $response->assertSessionHasErrors([
            'email' => 'Увійти можуть лише користувачі з корпоративної електронної адреси dspu.edu.ua.'
        ]);
    }
}
