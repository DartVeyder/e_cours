<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('platform.login'));

        $response->assertOk();
        $response->assertSee('E-COURS');
        $response->assertSee('Дрогобицький державний педагогічний університет імені Івана Франка');
        $response->assertSee('Вхід до системи');
        $response->assertSee('Увійти через Google');
        $response->assertSee('/auth/google/redirect');
        $response->assertSee('@dspu.edu.ua');
        $response->assertSee('Вхід для адміністраторів');
        $response->assertSee(route('platform.changelog'));
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($user)->get(route('platform.login'));

        $response->assertRedirect(route('platform.main'));
    }

    public function test_login_page_displays_error_messages_safely(): void
    {
        $response = $this->withSession([
            'errors' => collect(['email' => ['Увійти можуть лише користувачі з корпоративної електронної адреси dspu.edu.ua.']]),
        ])->get(route('platform.login'));

        $response->assertOk();
        $response->assertSee('Увійти можуть лише користувачі з корпоративної електронної адреси dspu.edu.ua.');
    }
}
