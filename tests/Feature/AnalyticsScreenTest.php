<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSpecialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class AnalyticsScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        $role = Role::create([
            'name' => 'Administrator',
            'slug' => 'administrator',
            'permissions' => [
                'platform.index' => 1,
                'platform.systems.students' => 1,
                'platform.systems.users' => 1,
                'platform.systems.roles' => 1,
            ],
        ]);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('platform.analytics'));
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_analytics_screen(): void
    {
        $admin = $this->createAdminUser();

        UserSpecialty::create([
            'email' => 'master2026@dspu.edu.ua',
            'card_id' => 'CARD-TEST-M26',
            'full_name' => 'Тестовий Магістр',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет історії',
            'specialty' => '032 Історія та археологія',
        ]);

        $response = $this->actingAs($admin)->get(route('platform.analytics'));

        $response->assertOk();
        $response->assertSee('Аналітика вибору дисциплін');
        $response->assertSee('Магістр');
        $response->assertSee('Факультет історії');
        $response->assertSee('032 Історія та археологія');
    }

    public function test_admin_can_export_analytics_to_excel(): void
    {
        $admin = $this->createAdminUser();

        UserSpecialty::create([
            'email' => 'master2026@dspu.edu.ua',
            'card_id' => 'CARD-TEST-M26',
            'full_name' => 'Тестовий Магістр',
            'study_start' => '2026-09-01',
            'degree' => 'Магістр',
            'department' => 'Факультет історії',
            'specialty' => '032 Історія та археологія',
        ]);

        $response = $this->actingAs($admin)->get(
            route('export.analytics.excel', ['entry_year' => '2026', 'degree' => 'Магістр'])
        );

        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition') ?? '', '.xlsx') ||
            str_contains($response->headers->get('content-type') ?? '', 'spreadsheet')
        );
    }
}
