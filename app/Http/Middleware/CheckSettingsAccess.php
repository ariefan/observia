<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSettingsAccess
{
    /**
     * Handle an incoming request.
     * Allows access to users who can access settings.
     *
     * Access granted to:
     * - Super users (global access)
     * - Farm owners
     * - Farm admins
     *
     * Denied to:
     * - Farmers
     * - Investors
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->canAccessSettings()) {
            return $next($request);
        }

        return redirect()->route('dashboard')
            ->with('error', 'Anda tidak memiliki akses ke pengaturan.');
    }
}
