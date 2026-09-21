<?php

namespace App\Http\Middleware;

use App\Models\IpBlacklist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIpBlacklist
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (IpBlacklist::isBlocked($ip)) {
            $record = IpBlacklist::getBlockedRecord($ip);
            $reason = $record ? $record->reason : 'Підозріла активність (DDoS / Brute force)';
            $blockedUntil = $record && $record->blocked_until
                ? $record->blocked_until->format('d.m.Y H:i')
                : 'безстроково';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'IP_BLACKLISTED',
                    'message' => 'Доступ заблоковано. Ваш IP занесено до чорного списку системи через підозрілу активність.',
                    'ip' => $ip,
                    'reason' => $reason,
                    'blocked_until' => $blockedUntil,
                ], 403);
            }

            return response()->view('errors.blacklisted', [
                'ip' => $ip,
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'telegram_url' => config('app.telegram_url', 'https://t.me/ddpu_support'),
            ], 403);
        }

        return $next($request);
    }
}
