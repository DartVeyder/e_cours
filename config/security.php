<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | IP addresses in this list will never be blacklisted by automatic DDoS
    | or brute-force rate limiters. Useful for localhost, office static IPs, etc.
    |
    */

    'ip_whitelist' => array_filter(array_merge(
        ['127.0.0.1', '::1'],
        explode(',', (string) env('IP_WHITELIST', ''))
    )),

    /*
    |--------------------------------------------------------------------------
    | Anti-DDoS Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Maximum allowed HTTP requests from a single IP per minute.
    | If an IP exceeds this threshold, it is automatically added to the blacklist.
    |
    */

    'ddos_limit_per_minute' => (int) env('DDOS_LIMIT_PER_MINUTE', 180),

    /*
    |--------------------------------------------------------------------------
    | Brute-Force Login Protection
    |--------------------------------------------------------------------------
    |
    | Limits on failed login attempts on the login form.
    |
    */

    'login_max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => 60,
    'login_max_failures_before_blacklist' => (int) env('LOGIN_MAX_FAILURES_BEFORE_BLACKLIST', 10),

    /*
    |--------------------------------------------------------------------------
    | Default Blacklist Duration (Hours)
    |--------------------------------------------------------------------------
    |
    | Duration for temporary IP blocks. Defaults to 24 hours.
    | Set to 0 or null for permanent blocks.
    |
    */

    'block_duration_hours' => (int) env('SECURITY_BLOCK_HOURS', 24),

];
