<?php

namespace App\Providers;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LoginLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Listen for successful login events
        Event::listen(Login::class, function ($event) {
            LoginLog::logLogin($event->user, [
                'guard' => $event->guard,
                'remember' => request()->has('remember'),
            ]);
        });

        // Listen for logout events
        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                LoginLog::logLogout($event->user, [
                    'guard' => $event->guard,
                ]);
            }
        });

        // Listen for failed login attempts
        Event::listen(Failed::class, function ($event) {
            LoginLog::logFailedLogin(
                $event->credentials['email'] ?? 'unknown',
                [
                    'guard' => $event->guard,
                    'credentials' => array_keys($event->credentials),
                ]
            );
        });
    }
}
