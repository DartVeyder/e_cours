<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');

        // Check if IP is blacklisted before processing any request
        $middleware->prepend(\App\Http\Middleware\CheckIpBlacklist::class);

        // Monitor request rate and detect DDoS abuse
        $middleware->append(\App\Http\Middleware\DetectDdosAbuse::class);

        // Protect login form against brute-force password guessing
        $middleware->web(append: [
            \App\Http\Middleware\ProtectLoginBruteForce::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
