<?php

namespace App\Http\Middleware;

use App\Enums\FarmRole;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $userFarms = collect();
        $permissions = $this->getDefaultPermissions();

        if ($user) {
            $user->load([
                'currentFarm' => function ($query) {
                    $query->withCount('users');
                }
            ]);

            if ($user->isSuperUser()) {
                // Super users can see all farms
                $userFarms = \App\Models\Farm::select('id', 'name', 'picture')->get();
            } else {
                // Regular users only see their associated farms
                $userFarms = $user->farms()
                    ->select('farms.id', 'farms.name', 'farms.picture')
                    ->get();
            }

            if ($user->current_farm_id) {
                // Get pivot role from many-to-many
                $pivot = $user->farms->firstWhere('id', $user->current_farm_id)?->pivot;
                $role = $pivot?->role;

                // Inject role and role label directly into currentFarm object
                if ($user->relationLoaded('currentFarm') && $user->currentFarm) {
                    $user->currentFarm->setAttribute('role', $role);
                    $farmRole = FarmRole::fromString($role);
                    $user->currentFarm->setAttribute('role_label', $farmRole?->label() ?? $role);
                }
            }

            // Build permissions object for frontend
            $permissions = $this->buildPermissions($user);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                'farms' => $userFarms,
                'permissions' => $permissions,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'farmRoles' => FarmRole::options(),
        ];
    }

    /**
     * Build permissions object for the authenticated user
     */
    private function buildPermissions($user): array
    {
        return [
            'isSuperUser' => $user->isSuperUser(),
            'canAccessFinance' => $user->canAccessFinance(),
            'canModifyFinance' => $user->canModifyFinance(),
            'canAccessOperations' => $user->canAccessOperations(),
            'canModifyOperations' => $user->canModifyOperations(),
            'canManageMembers' => $user->canManageMembers(),
            'canAccessSettings' => $user->canAccessSettings(),
            'isViewOnly' => $user->isViewOnly(),
        ];
    }

    /**
     * Get default permissions for unauthenticated users
     */
    private function getDefaultPermissions(): array
    {
        return [
            'isSuperUser' => false,
            'canAccessFinance' => false,
            'canModifyFinance' => false,
            'canAccessOperations' => false,
            'canModifyOperations' => false,
            'canManageMembers' => false,
            'canAccessSettings' => false,
            'isViewOnly' => false,
        ];
    }
}
