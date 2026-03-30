<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\UpdateLastSeen;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // ── Inventory alerts: daily at 7 AM ──────────────────────────────────
        $schedule->command('alerts:inventory')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('alerts:inventory command failed');
            });

        // ── Overdue task alerts: every hour during working hours ──────────────
        $schedule->command('alerts:overdue-tasks')
            ->hourlyAt(0)
            ->between('08:00', '20:00')
            ->withoutOverlapping();

        // ── Cancel expired appointments: daily just after midnight ────────────
        $schedule->command('app:cancel-expired-appointments')
            ->dailyAt('00:01')
            ->withoutOverlapping();

        // ── Reset nurse daily workload: daily at midnight ─────────────────────
        $schedule->command('nurse:reset-workload')
            ->dailyAt('00:05')
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Enable CORS for API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // ✅ Apply UpdateLastSeen to ALL web routes automatically
        $middleware->web(append: [
            UpdateLastSeen::class,
        ]);

        // Register custom route middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();