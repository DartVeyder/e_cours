<?php

namespace Tests\Feature;

use App\Models\IpBlacklist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BruteForceAndDdosProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_blacklisted_ip_is_blocked_globally_with_403(): void
    {
        $attackerIp = '203.0.113.10';
        IpBlacklist::blockIp($attackerIp, 'Тестове блокування', 24);

        // Web request gets custom 403 error page
        $response = $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
            ->get('/login');

        $response->assertStatus(403);
        $response->assertSee('Доступ тимчасово обмежено');
        $response->assertSee('Тестове блокування');
        $response->assertSee($attackerIp);

        // JSON / API request gets JSON 403
        $apiResponse = $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
            ->getJson('/api/students-subjects');

        $apiResponse->assertStatus(403);
        $apiResponse->assertJson([
            'error' => 'IP_BLACKLISTED',
            'ip' => $attackerIp,
        ]);
    }

    public function test_login_rate_limiter_blocks_after_max_attempts(): void
    {
        Config::set('security.login_max_attempts', 5);
        $attackerIp = '203.0.113.20';

        // 5 failed login attempts
        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
                ->post('/login', [
                    'email' => 'victim@example.com',
                    'password' => 'wrong-password-' . $i,
                ]);
        }

        // 6th attempt should be blocked by RateLimiter
        $response = $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
            ->post('/login', [
                'email' => 'victim@example.com',
                'password' => 'wrong-password-6',
            ]);

        $response->assertSessionHasErrors(['email']);
        $error = session('errors')->first('email');
        $this->assertStringContainsString('Забагато невдалих спроб входу', $error);
    }

    public function test_repeated_login_failures_automatically_blacklist_ip(): void
    {
        Config::set('security.login_max_attempts', 15);
        Config::set('security.login_max_failures_before_blacklist', 10);
        $attackerIp = '203.0.113.30';

        for ($i = 1; $i <= 9; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
                ->post('/login', [
                    'email' => 'victim@example.com',
                    'password' => 'wrong-' . $i,
                ]);
            $this->assertFalse(IpBlacklist::isBlocked($attackerIp));
        }

        // 10th failed attempt triggers automatic blacklisting
        $response = $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
            ->post('/login', [
                'email' => 'victim@example.com',
                'password' => 'wrong-10',
            ]);

        $response->assertStatus(403);
        $this->assertTrue(IpBlacklist::isBlocked($attackerIp));
        $this->assertDatabaseHas('ip_blacklists', [
            'ip_address' => $attackerIp,
        ]);
    }

    public function test_successful_login_clears_rate_limiter_and_failure_count(): void
    {
        $user = User::factory()->create([
            'email' => 'student@dspu.edu.ua',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $userIp = '203.0.113.40';

        // 2 failed attempts
        for ($i = 1; $i <= 2; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $userIp])
                ->post('/login', [
                    'email' => 'student@dspu.edu.ua',
                    'password' => 'wrong-pass',
                ]);
        }

        $this->assertEquals(2, Cache::get('login_failures:' . $userIp));

        // Successful attempt
        $successResponse = $this->withServerVariables(['REMOTE_ADDR' => $userIp])
            ->post('/login', [
                'email' => 'student@dspu.edu.ua',
                'password' => 'CorrectPassword123!',
            ]);

        $successResponse->assertRedirect();
        $this->assertNull(Cache::get('login_failures:' . $userIp));
    }

    public function test_ddos_detection_automatically_blacklists_ip_when_threshold_exceeded(): void
    {
        Config::set('security.ddos_limit_per_minute', 5);
        $dosIp = '203.0.113.50';

        // Send 5 requests (within limit)
        for ($i = 1; $i <= 5; $i++) {
            $resp = $this->withServerVariables(['REMOTE_ADDR' => $dosIp])->get('/login');
            $this->assertNotEquals(403, $resp->status());
        }

        $this->assertFalse(IpBlacklist::isBlocked($dosIp));

        // 6th request exceeds threshold -> gets blocked with 403
        $blockedResp = $this->withServerVariables(['REMOTE_ADDR' => $dosIp])->get('/login');
        $blockedResp->assertStatus(403);

        $this->assertTrue(IpBlacklist::isBlocked($dosIp));
        $this->assertDatabaseHas('ip_blacklists', [
            'ip_address' => $dosIp,
        ]);
    }

    public function test_whitelisted_ip_is_never_blacklisted_by_ddos_detection(): void
    {
        Config::set('security.ddos_limit_per_minute', 3);
        $safeIp = '127.0.0.1';

        for ($i = 1; $i <= 10; $i++) {
            $resp = $this->withServerVariables(['REMOTE_ADDR' => $safeIp])->get('/login');
            $this->assertNotEquals(403, $resp->status());
        }

        $this->assertFalse(IpBlacklist::isBlocked($safeIp));
    }

    public function test_admin_can_view_and_unblock_ip_via_orchid_screen(): void
    {
        $admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
            ],
        ]);

        $ip = '203.0.113.60';
        IpBlacklist::blockIp($ip, 'Брутфорс підбір паролів', 24);
        $this->assertTrue(IpBlacklist::isBlocked($ip));

        // Admin can view the blacklist screen
        $viewResp = $this->actingAs($admin)->get(route('platform.systems.ip-blacklist'));
        $viewResp->assertOk();
        $viewResp->assertSee($ip);

        // Admin triggers unblock action
        $unblockResp = $this->actingAs($admin)->post(route('platform.systems.ip-blacklist') . '/unblock', [
            'ip' => $ip,
        ]);
        $unblockResp->assertRedirect();

        $this->assertFalse(IpBlacklist::isBlocked($ip));
        $this->assertDatabaseMissing('ip_blacklists', [
            'ip_address' => $ip,
        ]);
    }

    public function test_admin_can_manually_block_ip_via_screen(): void
    {
        $admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
            ],
        ]);

        $ip = '203.0.113.70';

        $blockResp = $this->actingAs($admin)->post(route('platform.systems.ip-blacklist') . '/manualBlock', [
            'ip' => $ip,
            'reason' => 'Ручне блокування підозрілого хоста',
            'hours' => 12,
        ]);
        $blockResp->assertRedirect();

        $this->assertTrue(IpBlacklist::isBlocked($ip));
        $this->assertDatabaseHas('ip_blacklists', [
            'ip_address' => $ip,
            'reason' => 'Ручне блокування підозрілого хоста',
        ]);
    }
}
