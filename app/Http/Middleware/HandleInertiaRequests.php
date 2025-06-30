<?php

namespace App\Http\Middleware;

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

        if ($user) {
            $user->load([
                'currentFarm' => function ($query) {
                    $query->withCount('users');
                }
            ]);

            $userFarms = $user->farms()
                ->select('farms.id', 'farms.name', 'farms.picture')
                ->get();

            if ($user->current_farm_id) {
                // Get pivot role from many-to-many
                $pivot = $user->farms->firstWhere('id', $user->current_farm_id)?->pivot;
                $role = $pivot?->role;

                // Inject role directly into currentFarm object
                if ($user->relationLoaded('currentFarm') && $user->currentFarm) {
                    $user->currentFarm->setAttribute('role', $role);
                }
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                'farms' => $userFarms,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
