<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.context' => \App\Http\Middleware\SetTenantContext::class,
            'tenant.scope' => \App\Http\Middleware\TenantScope::class,
            'onboarding.complete' => \App\Http\Middleware\EnsureOnboardingComplete::class,
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'subscription.check' => \App\Http\Middleware\CheckSubscriptionLimits::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure.operational' => \App\Http\Middleware\EnsureOperationalCompany::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
