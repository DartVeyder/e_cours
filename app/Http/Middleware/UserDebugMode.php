<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserDebugMode
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->debug_mode) {
            config(['app.debug' => true]);
        }

        return $next($request);
    }
}
