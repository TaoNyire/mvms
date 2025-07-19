<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware; // ← import your middleware
use App\Http\Middleware\WebRoleMiddleware;
use App\Http\Middleware\VerifiedUserRole;
use App\Http\Middleware\ExcludeAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'web.role' => WebRoleMiddleware::class,
            'volunteer.profile.complete' => \App\Http\Middleware\EnsureVolunteerProfileComplete::class,
            'organization.profile.complete' => \App\Http\Middleware\EnsureOrganizationProfileComplete::class,
            'verified.user.role' => VerifiedUserRole::class,
            'exclude.admin' => ExcludeAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
