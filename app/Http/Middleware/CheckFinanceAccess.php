<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFinanceAccess
{
    /**
     * Handle an incoming request.
     * Only allows access to users who are super users or have finance/admin roles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super users can access everything
        if ($user->is_super_user) {
            return $next($request);
        }

        // Check if user has finance or admin role in any farm they belong to
        $hasFinanceAccess = $user->farms()
            ->wherePivotIn('role', ['admin', 'owner', 'finance'])
            ->exists();

        if ($hasFinanceAccess) {
            return $next($request);
        }

        // No access - redirect to farmer view
        return redirect()->route('payments.farmer')
            ->with('error', 'Anda tidak memiliki akses ke halaman keuangan.');
    }
}
