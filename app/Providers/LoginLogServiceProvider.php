<?php

namespace App\Providers;

use App\Models\LoginLog;
use App\Notifications\LoginTelegramNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
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

            // Send Telegram notification for login
            $this->sendLoginNotification($event->user, $event->remember);
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

    /**
     * Send Telegram notification for successful login
     */
    private function sendLoginNotification($user, $remember = false): void
    {
        try {
            // Determine login method
            $loginMethod = 'Email & Password';
            $currentRoute = request()->route()->getName() ?? '';
            
            if ($currentRoute === 'google.callback' || str_contains(request()->url(), 'google')) {
                $loginMethod = 'Google OAuth';
            }

            // Create a dummy notifiable (we'll send to default chat anyway)
            $notifiable = new class {
                public function routeNotificationForTelegram() {
                    return null; // Use default chat
                }
            };

            Notification::send($notifiable, new LoginTelegramNotification([
                'type' => 'login',
                'title' => 'Pengguna Masuk ke Sistem',
                'message' => $user->name . ' berhasil masuk ke sistem AI Farm.',
                'user_name' => $user->name,
                'user_email' => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_method' => $loginMethod . ($remember ? ' (Remember Me)' : ''),
                'login_time' => now()->format('d/m/Y H:i:s'),
            ]));
        } catch (\Exception $e) {
            // Log error but don't block login process
            \Illuminate\Support\Facades\Log::error('Failed to send login Telegram notification: ' . $e->getMessage());
        }
    }
}
