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
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/bridge/heartbeat',
            '/bridge/sync'
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role'         => \App\Http\Middleware\CheckRole::class,
            '2fa.verified' => \App\Http\Middleware\Ensure2FAVerified::class,
        ]);
    })

    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('registrations:check-expiry')->dailyAt('00:00');
        $schedule->command('tags:send-expiry-reminders')->dailyAt('08:00');
        $schedule->job(new \App\Jobs\SendWeeklyTrafficReport)->fridays()->at('17:00');
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
