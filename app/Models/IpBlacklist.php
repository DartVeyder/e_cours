<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class IpBlacklist extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'ip_address',
        'reason',
        'request_count',
        'blocked_until',
    ];

    protected function casts(): array
    {
        return [
            'blocked_until' => 'datetime',
            'request_count' => 'integer',
        ];
    }

    /**
     * The attributes for which you can use filters in Orchid.
     *
     * @var array
     */
    protected $allowedFilters = [
        'ip_address',
        'reason',
    ];

    /**
     * The attributes for which can use sort in Orchid.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'ip_address',
        'request_count',
        'blocked_until',
        'created_at',
    ];

    /**
     * Check whether an IP is currently blocked (using cache-first pattern).
     */
    public static function isBlocked(string $ip): bool
    {
        $cacheKey = 'ip_blocked:' . $ip;

        return (bool) Cache::remember($cacheKey, 60, function () use ($ip) {
            $record = static::where('ip_address', $ip)->first();

            if (! $record) {
                return false;
            }

            if ($record->blocked_until !== null && $record->blocked_until->isPast()) {
                $record->delete();
                return false;
            }

            return true;
        });
    }

    /**
     * Get the active blacklist record for given IP.
     */
    public static function getBlockedRecord(string $ip): ?self
    {
        $record = static::where('ip_address', $ip)->first();

        if (! $record) {
            return null;
        }

        if ($record->blocked_until !== null && $record->blocked_until->isPast()) {
            $record->delete();
            Cache::forget('ip_blocked:' . $ip);
            return null;
        }

        return $record;
    }

    /**
     * Add an IP to the blacklist.
     */
    public static function blockIp(string $ip, string $reason, ?int $hours = 24, int $requestCount = 0): self
    {
        $blockedUntil = $hours ? Carbon::now()->addHours($hours) : null;

        $record = static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'request_count' => $requestCount,
            ]
        );

        $ttl = $hours ? Carbon::now()->addHours($hours) : Carbon::now()->addYears(10);
        Cache::put('ip_blocked:' . $ip, true, $ttl);

        if (function_exists('activity')) {
            activity()
                ->withProperties([
                    'ip' => $ip,
                    'reason' => $reason,
                    'blocked_until' => $blockedUntil?->toDateTimeString(),
                    'request_count' => $requestCount,
                ])
                ->log("IP-адресу {$ip} занесено до чорного списку");
        }

        return $record;
    }

    /**
     * Remove an IP from the blacklist.
     */
    public static function unblockIp(string $ip): bool
    {
        Cache::forget('ip_blocked:' . $ip);
        Cache::forget('login_failures:' . $ip);
        Cache::forget('login_strikes:' . $ip);

        $deleted = static::where('ip_address', $ip)->delete();

        if ($deleted && function_exists('activity')) {
            activity()
                ->withProperties(['ip' => $ip])
                ->log("IP-адресу {$ip} вилучено з чорного списку");
        }

        return (bool) $deleted;
    }
}
