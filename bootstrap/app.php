<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\CheckFinanceAccess;
use App\Http\Middleware\CheckSuperUser;
use App\Http\Middleware\CheckOperationsAccess;
use App\Http\Middleware\CheckSettingsAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register middleware aliases for role-based access control
        $middleware->alias([
            'super.user' => CheckSuperUser::class,           // Super user only
            'finance.access' => CheckFinanceAccess::class,   // Owner, Admin, Investor
            'operations.access' => CheckOperationsAccess::class, // Owner, Admin, Farmer
            'settings.access' => CheckSettingsAccess::class, // Owner, Admin
        ]);

        // Exclude Telegram webhook from CSRF protection
        $middleware->validateCsrfTokens(except: [
            '/telegram/webhook'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
