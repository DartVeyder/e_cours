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
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create(['key' => 'subject_selection_enabled', 'value' => '1']);
    }

    public function test_guest_is_redirected_from_all_protected_screens(): void
    {
        $routes = [
            route('platform.settings'),
            route('platform.settings.google-sheets'),
            route('platform.logs'),
            route('platform.activity.logs'),
            route('platform.groups'),
            route('platform.students'),
            route('platform.subjects'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_student_gets_forbidden_on_admin_screens(): void
    {
        $student = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $routes = [
            route('platform.settings'),
            route('platform.settings.google-sheets'),
            route('platform.logs'),
            route('platform.activity.logs'),
            route('platform.groups'),
            route('platform.students'),
            route('platform.subjects'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($student)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_student_cannot_switch_to_another_students_specialty_cookie_idor(): void
    {
        $studentA = User::factory()->create(['name' => 'Студент А', 'permissions' => ['platform.index' => true]]);
        $studentB = User::factory()->create(['name' => 'Студент Б', 'permissions' => ['platform.index' => true]]);

        $group = Group::create(['name' => 'ІПЗ-21']);
        $specA = UserSpecialty::create([
            'user_id' => $studentA->id,
            'email' => $studentA->email,
            'card_id' => 'CARD-A',
            'full_name' => 'Студент А',
            'group_id' => $group->id,
            'group_name' => 'ІПЗ-21',
            'specialty' => '121 ІПЗ',
        ]);

        $specB = UserSpecialty::create([
            'user_id' => $studentB->id,
            'email' => $studentB->email,
            'card_id' => 'CARD-B',
            'full_name' => 'Студент Б',
            'group_id' => $group->id,
            'group_name' => 'ІПЗ-21',
            'specialty' => '122 Комп науки',
        ]);

        // Student A tries to forge cookie to point to Student B's specialty on MainScreen
        $response = $this->actingAs($studentA)
            ->withCookie('user_specialty_id', (string) $specB->id)
            ->get(route('platform.main'));

        $response->assertOk();
        // Since Student A only has 1 specialty, it should auto-select Student A's specialty, NOT Student B's
        $response->assertSee('Студент А');
        $response->assertDontSee('Студент Б');

        // Student A tries to post chooseSpecialty for Student B's specialty
        $postResponse = $this->actingAs($studentA)
            ->post(route('platform.main') . '/chooseSpecialty', [
                'id' => $specB->id,
                'text' => 'Чужа спеціальність',
            ]);

        // Should not set cookie to Student B's specialty
        $this->assertNotEquals((string) $specB->id, $postResponse->getCookie('user_specialty_id')?->getValue());
    }

    public function test_student_cannot_select_subjects_for_another_student_idor(): void
    {
        $studentA = User::factory()->create(['name' => 'Зловмисник', 'permissions' => ['platform.index' => true]]);
        $studentB = User::factory()->create(['name' => 'Жертва', 'permissions' => ['platform.index' => true]]);

        $group = Group::create(['name' => 'ІПЗ-21', 'semester_count' => 8]);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specA = UserSpecialty::create([
            'user_id' => $studentA->id,
            'email' => $studentA->email,
            'card_id' => 'CARD-A',
            'full_name' => 'Зловмисник',
            'group_id' => $group->id,
            'specialty' => '121 ІПЗ',
        ]);

        $specB = UserSpecialty::create([
            'user_id' => $studentB->id,
            'email' => $studentB->email,
            'card_id' => 'CARD-B',
            'full_name' => 'Жертва',
            'group_id' => $group->id,
            'specialty' => '121 ІПЗ',
        ]);

        $subject = Subject::create(['name' => 'Штучний Інтелект', 'active' => 1]);

        // Student A tries to choose a subject using Student B's specialty ID in cookie
        $response = $this->actingAs($studentA)
            ->withCookie('user_specialty_id', (string) $specB->id)
            ->post('/selsubjects/chooseSubject?subjectId=' . $subject->id . '&subjectName=' . urlencode($subject->name) . '&semester=1');

        // Subject must NOT be selected for student B
        $this->assertDatabaseMissing('user_specialty_subjects', [
            'user_specialty_id' => $specB->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_admin_can_access_protected_screens(): void
    {
        $admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
                'platform.systems.students' => true,
                'platform.systems.subjects' => true,
                'platform.systems.groups' => true,
                'platform.systems.logs' => true,
            ],
        ]);

        $routes = [
            route('platform.settings'),
            route('platform.settings.google-sheets'),
            route('platform.logs'),
            route('platform.activity.logs'),
            route('platform.groups'),
            route('platform.students'),
            route('platform.subjects'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertOk();
        }
    }

    public function test_student_without_permission_cannot_export_analytics_excel(): void
    {
        $student = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($student)->get(route('export.analytics.excel'));
        $response->assertStatus(403);
    }

    public function test_dekanat_cannot_access_or_edit_group_from_another_department(): void
    {
        $deptMath = \App\Models\Department::create(['name' => 'Математичний факультет', 'abbreviation' => 'МАТ']);
        $deptPhilology = \App\Models\Department::create(['name' => 'Філологічний факультет', 'abbreviation' => 'ФІЛ']);

        $role = \Orchid\Platform\Models\Role::firstOrCreate(['slug' => 'dekanat'], [
            'name' => 'Деканат',
            'permissions' => ['platform.index' => 1, 'platform.systems.groups' => 1],
        ]);

        $dekanatMathUser = User::factory()->create([
            'department_id' => $deptMath->id,
        ]);
        $dekanatMathUser->roles()->attach($role);

        $philologyGroup = Group::create([
            'name' => 'УКР-11',
            'department_id' => $deptPhilology->id,
            'semester_count' => 8,
        ]);

        // Trying to view the edit screen of another department's group
        $responseQuery = $this->actingAs($dekanatMathUser)
            ->get(route('platform.groups.edit', $philologyGroup->id));
        $responseQuery->assertStatus(403);

        // Trying to save/edit another department's group
        $responseSave = $this->actingAs($dekanatMathUser)
            ->post(route('platform.groups.edit', $philologyGroup->id) . '/save', [
                'group' => ['semester_count' => 10],
            ]);
        $responseSave->assertStatus(403);
    }

    public function test_user_specialty_hides_sensitive_pii_fields_in_json_and_array(): void
    {
        $specialty = new UserSpecialty([
            'card_id' => '12345678',
            'full_name' => 'Петро Порошенко',
            'rnokpp' => '1234567890',
            'valid_rnokpp' => 'Так',
            'document_series' => 'АА',
            'document_number' => '123456',
            'birth_date' => '2000-01-01',
            'citizenship' => 'Україна',
        ]);

        $array = $specialty->toArray();

        $this->assertArrayNotHasKey('rnokpp', $array);
        $this->assertArrayNotHasKey('valid_rnokpp', $array);
        $this->assertArrayNotHasKey('document_series', $array);
        $this->assertArrayNotHasKey('document_number', $array);
        $this->assertArrayNotHasKey('birth_date', $array);
        $this->assertArrayNotHasKey('citizenship', $array);

        $this->assertArrayHasKey('full_name', $array);
        $this->assertArrayHasKey('card_id', $array);
    }

    public function test_orchid_demo_routes_are_disabled(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('platform.example'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('platform.example.fields'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('platform.example.charts'));
    }

    public function test_export_and_api_endpoints_have_rate_limiting(): void
    {
        $groupExcelRoute = \Illuminate\Support\Facades\Route::getRoutes()->getByName('export.group.excel');
        $this->assertNotNull($groupExcelRoute);
        $this->assertContains('throttle:20,1', $groupExcelRoute->gatherMiddleware());

        $analyticsExcelRoute = \Illuminate\Support\Facades\Route::getRoutes()->getByName('export.analytics.excel');
        $this->assertNotNull($analyticsExcelRoute);
        $this->assertContains('throttle:20,1', $analyticsExcelRoute->gatherMiddleware());

        $apiRoute = collect(\Illuminate\Support\Facades\Route::getRoutes()->get('GET'))->first(function ($route) {
            return $route->uri() === 'api/students-subjects';
        });
        $this->assertNotNull($apiRoute);
        $this->assertContains('throttle:30,1', $apiRoute->gatherMiddleware());
    }
}

