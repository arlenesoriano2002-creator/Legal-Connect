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
   ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->alias([
            'verified_user' => \App\Http\Middleware\VerifyUser::class,
            'client_only' => \App\Http\Middleware\CheckClientonlyAccess::class,
            'ensure_client' => \App\Http\Middleware\EnsureClientRole::class,
            'validate_tab_token' => \App\Http\Middleware\ValidateTabToken::class,
            'validate.tab.session' => \App\Http\Middleware\ValidateTabSession::class,
            'authorize.role' => \App\Http\Middleware\AuthorizeRole::class,
            'enforce.single.session' => \App\Http\Middleware\EnforceSingleSession::class,
            // Security system middleware
            'force-logout' => \App\Http\Middleware\ForceLogoutOnLogin::class,
            'guest.offline' => \App\Http\Middleware\MarkGuestUserOffline::class,
            'session-timeout' => \App\Http\Middleware\SessionTimeout::class,
            'check-inactivity' => \App\Http\Middleware\CheckSessionInactivity::class,
            'cache-control' => \App\Http\Middleware\CacheControlMiddleware::class,
            'protect-back-nav' => \App\Http\Middleware\ProtectBackNavigation::class,
        ]);
    })


    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
