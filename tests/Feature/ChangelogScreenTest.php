<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangelogScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_changelog(): void
    {
        $response = $this->get(route('platform.changelog'));
        $response->assertRedirect(route('platform.login'));
    }

    public function test_authenticated_user_can_access_changelog_screen(): void
    {
        $user = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($user)->get(route('platform.changelog'));

        $response->assertOk();
        $response->assertSee('Журнал змін (Changelog)');
        $response->assertSee('Історія оновлень платформи E-Cours');
        $response->assertSee('v' . config('app.version', '1.5.0'));
        $response->assertSee('Поточна версія');
        $response->assertSee('1.5.0');
        $response->assertSee('1.4.1');
        $response->assertSee('1.4.0');
    }

    public function test_footer_has_clickable_changelog_link(): void
    {
        $user = User::factory()->create([
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertOk();
        $response->assertSee(route('platform.changelog'));
    }
}
