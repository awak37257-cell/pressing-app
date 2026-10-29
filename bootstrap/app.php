<?php

use App\Http\Middleware\CheckPasswordChange;
use App\Http\Middleware\EnsureUserHasPressing;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enregistrement des alias
        $middleware->alias([
            'role'     => RoleMiddleware::class,
            'pressing' => EnsureUserHasPressing::class,
            'password' => CheckPasswordChange::class,
        ]);

        // AJOUT ICI : Exécuter ce middleware automatiquement sur toutes les pages web authentifiées
        $middleware->web(append: [
            CheckPasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();