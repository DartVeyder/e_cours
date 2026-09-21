<?php

namespace App\Http\Middleware;

use App\Models\IpBlacklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DetectDdosAbuse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $whitelist = (array) config('security.ip_whitelist', ['127.0.0.1', '::1']);
        if (in_array($ip, $whitelist, true)) {
            return $next($request);
        }

        $window = floor(time() / 60);
        $cacheKey = "ddos_req:{$ip}:{$window}";
        $limit = (int) config('security.ddos_limit_per_minute', 180);

        $count = (int) Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, 75);

        if ($count > $limit) {
            $durationHours = (int) config('security.block_duration_hours', 24);
            $reason = "Підозра на DDoS-атаку (перевищено ліміт: {$count} запитів/хв)";

            IpBlacklist::blockIp($ip, $reason, $durationHours, $count);

            Log::warning("Anti-DDoS: IP {$ip} automatically blacklisted. Request count: {$count}/min");

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'IP_BLACKLISTED_DDOS',
                    'message' => 'Доступ заблоковано через підозру на DDoS-атаку (надмірна частота запитів).',
                    'ip' => $ip,
                    'reason' => $reason,
                ], 403);
            }

            return response()->view('errors.blacklisted', [
                'ip' => $ip,
                'reason' => $reason,
                'blocked_until' => now()->addHours($durationHours)->format('d.m.Y H:i'),
                'telegram_url' => config('app.telegram_url', 'https://t.me/ddpu_support'),
            ], 403);
        }

        return $next($request);
    }
}
