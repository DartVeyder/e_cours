<?php

namespace App\Http\Middleware;

use App\Models\IpBlacklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProtectLoginBruteForce
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! ($request->isMethod('POST') && ($request->is('login') || $request->routeIs('platform.login.auth')))) {
            return $next($request);
        }

        $ip = $request->ip();
        $email = strtolower((string) $request->input('email', ''));

        // 1. Check if IP is already in blacklist
        if (IpBlacklist::isBlocked($ip)) {
            abort(403, 'Ваш IP-адрес занесено до чорного списку через підозрілу активність.');
        }

        $maxAttempts = (int) config('security.login_max_attempts', 5);
        $decaySeconds = (int) config('security.login_decay_seconds', 60);
        $maxFailures = (int) config('security.login_max_failures_before_blacklist', 10);
        $blockHours = (int) config('security.block_duration_hours', 24);

        $ipLimiterKey = 'login_throttle_ip:' . $ip;
        $userLimiterKey = 'login_throttle_user:' . sha1($email . '|' . $ip);

        // 2. Check if currently throttled / locked out
        if (RateLimiter::tooManyAttempts($ipLimiterKey, $maxAttempts) || RateLimiter::tooManyAttempts($userLimiterKey, $maxAttempts)) {
            $seconds = max(
                RateLimiter::availableIn($ipLimiterKey),
                RateLimiter::availableIn($userLimiterKey)
            );

            // Escalate if continuous hammering during lockout
            $strikesKey = 'login_strikes:' . $ip;
            $strikes = (int) Cache::get($strikesKey, 0) + 1;
            Cache::put($strikesKey, $strikes, 900);

            if ($strikes >= $maxFailures) {
                IpBlacklist::blockIp(
                    $ip,
                    "Брутфорс підбір паролів (продовження спроб під час блокування: {$strikes} разів)",
                    $blockHours,
                    $strikes
                );
                abort(403, 'Ваш IP-адрес занесено до чорного списку через багаторазові спроби брутфорсу.');
            }

            throw ValidationException::withMessages([
                'email' => "Забагато невдалих спроб входу. Будь ласка, зачекайте {$seconds} сек. перед наступною спробою.",
            ]);
        }

        // 3. Process the login request
        try {
            $response = $next($request);
        } catch (ValidationException $e) {
            $this->recordFailure($ip, $ipLimiterKey, $userLimiterKey, $decaySeconds, $maxFailures, $blockHours);
            throw $e;
        }

        // Check if user is authenticated (login succeeded)
        $guard = config('platform.guard', 'web');
        if (Auth::guard($guard)->check()) {
            RateLimiter::clear($ipLimiterKey);
            RateLimiter::clear($userLimiterKey);
            Cache::forget('login_failures:' . $ip);
            Cache::forget('login_strikes:' . $ip);

            return $response;
        }

        // If not authenticated and response is a redirect with errors (login failed)
        if ($response->isRedirection() && session()->has('errors')) {
            $this->recordFailure($ip, $ipLimiterKey, $userLimiterKey, $decaySeconds, $maxFailures, $blockHours);
            return $response;
        }

        return $response;
    }

    /**
     * Record a failed login attempt and escalate to blacklist if threshold reached.
     */
    protected function recordFailure(
        string $ip,
        string $ipLimiterKey,
        string $userLimiterKey,
        int $decaySeconds,
        int $maxFailures,
        int $blockHours
    ): void {
        RateLimiter::hit($ipLimiterKey, $decaySeconds);
        RateLimiter::hit($userLimiterKey, $decaySeconds);

        $failuresKey = 'login_failures:' . $ip;
        $failures = (int) Cache::get($failuresKey, 0) + 1;
        Cache::put($failuresKey, $failures, 900);

        if ($failures >= $maxFailures) {
            IpBlacklist::blockIp(
                $ip,
                "Брутфорс підбір паролів при авторизації ({$failures} послідовних невдалих спроб)",
                $blockHours,
                $failures
            );
            abort(403, 'Ваш IP-адрес занесено до чорного списку через багаторазові невдалі спроби входу.');
        }
    }
}
