<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\UpdateLastSeen; // ADD THIS

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom route middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            //'track.online' => UpdateLastSeen::class, // ADD THIS
        ]);

        // Or apply to auth middleware group
        //$middleware->appendToGroup('auth', [
            //UpdateLastSeen::class,
        //]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();