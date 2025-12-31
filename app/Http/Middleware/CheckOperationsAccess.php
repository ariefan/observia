<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOperationsAccess
{
    /**
     * Handle an incoming request.
     * Allows access to users who can access operational features.
     *
     * Access granted to:
     * - Super users (global access)
     * - Farm owners
     * - Farm admins
     * - Farmers
     *
     * Denied to:
     * - Investors (view-only access)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->canAccessOperations()) {
            return $next($request);
        }

        return redirect()->route('dashboard')
            ->with('error', 'Anda tidak memiliki akses ke fitur operasional. Akun Anda hanya memiliki akses baca.');
    }
}
