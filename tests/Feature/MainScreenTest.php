<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_main_screen(): void
    {
        $response = $this->get(route('platform.main'));
        $response->assertRedirect('/login');
    }

    public function test_student_views_student_specific_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Студент',
            'permissions' => ['platform.index' => true],
        ]);

        Setting::create(['key' => 'subject_selection_enabled', 'value' => '1']);
        $group = Group::create(['name' => 'ІПЗ-21']);
        $student = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-12345',
            'full_name' => 'Іваненко Іван',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group->id,
            'specialty' => '121 Інженерія програмного забезпечення',
        ]);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertOk();
        $response->assertSee('Особистий кабінет студента');
        $response->assertSee('Вибір дисциплін ВІДКРИТО');
        $response->assertSee('Іваненко Іван');
        $response->assertSee('ІПЗ-21');
        $response->assertSee('Дані студента');
        $response->assertSee('Стан вибору дисциплін');
        $response->assertSee('Покрокова інструкція вибору дисциплін');
        $response->assertSee('Важливі правила та запитання');
        $response->assertDontSee('Панель Адміністратора');
        $response->assertDontSee('Модулі керування');
    }

    public function test_admin_views_admin_specific_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Головний Адміністратор',
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
                'platform.systems.users' => true,
            ],
        ]);

        Setting::create(['key' => 'subject_selection_enabled', 'value' => '1']);
        Group::create(['name' => 'ІПЗ-21']);
        Subject::create(['name' => 'Веб-програмування', 'active' => 1]);

        $response = $this->actingAs($admin)->get(route('platform.main'));

        $response->assertOk();
        $response->assertSee('Панель Адміністратора');
        $response->assertSee('Студенти');
        $response->assertSee('Дисципліни');
        $response->assertSee('Групи');
        $response->assertSee('Вибори');
        $response->assertSee('Модулі керування');
        $response->assertSee('Останні дії в системі');
        $response->assertSee('Інструкція та регламент роботи адміністратора / деканату');
        $response->assertDontSee('Особистий кабінет студента');
    }

    public function test_main_screen_displays_closed_status_when_selection_disabled(): void
    {
        $user = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);
        Setting::create(['key' => 'subject_selection_enabled', 'value' => '0']);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertOk();
        $response->assertSee('Вибір дисциплін ЗАКРИТО');
    }

    public function test_main_screen_displays_system_version(): void
    {
        $user = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertOk();
        $response->assertSee('v' . config('app.version', '1.4.0'));
    }
}
