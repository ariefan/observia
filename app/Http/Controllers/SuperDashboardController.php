<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\User;
use App\Models\Livestock;
use App\Models\Herd;
use App\Models\Ration;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuperDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is superuser
        if (!auth()->user()->is_super_user) {
            abort(403, 'Access denied. Superuser privileges required.');
        }

        // Get selected farm ID from request
        $selectedFarmId = $request->get('farm_id');
        $selectedFarm = null;
        
        if ($selectedFarmId && $selectedFarmId !== 'all') {
            $selectedFarm = Farm::find($selectedFarmId);
        }

        // Get comprehensive statistics
        $stats = [
            // Farm statistics
            'total_farms' => Farm::count(),
            'active_farms' => Farm::whereHas('users')->count(),
            'farms_with_livestock' => Farm::whereHas('livestocks')->count(),
            
            // User statistics
            'total_users' => User::count(),
            'super_users' => User::where('is_super_user', true)->count(),
            'users_with_farms' => User::whereHas('farms')->count(),
            
            // Livestock statistics
            'total_livestock' => Livestock::count(),
            'male_livestock' => Livestock::where('sex', 'M')->count(),
            'female_livestock' => Livestock::where('sex', 'F')->count(),
            'livestock_with_photos' => Livestock::whereNotNull('photo')
                ->whereRaw("photo::text != '[]'")
                ->whereRaw("photo::text != 'null'")
                ->whereRaw("json_array_length(photo::json) > 0")
                ->count(),
            
            // Herd statistics
            'total_herds' => Herd::count(),
            'herds_with_livestock' => Herd::whereHas('livestocks')->count(),
            
            // Feed and Ration statistics
            'total_rations' => Ration::count(),
            'total_feeds' => Feed::count(),
            
            // Recent activity
            'farms_created_last_30_days' => Farm::where('created_at', '>=', now()->subDays(30))->count(),
            'users_registered_last_30_days' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'livestock_added_last_30_days' => Livestock::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // Get top farms by livestock count
        $topFarmsByLivestock = Farm::withCount('livestocks')
            ->orderBy('livestocks_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($farm) {
                return [
                    'id' => $farm->id,
                    'name' => $farm->name,
                    'livestock_count' => $farm->livestocks_count,
                    'location' => $farm->address ?? 'Unknown',
                ];
            });

        // Get top farms by user count
        $topFarmsByUsers = Farm::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($farm) {
                return [
                    'id' => $farm->id,
                    'name' => $farm->name,
                    'users_count' => $farm->users_count,
                    'location' => $farm->address ?? 'Unknown',
                ];
            });

        // Get farms by province/region through city relationship
        $farmsByRegion = Farm::join('cities', 'farms.city_id', '=', 'cities.id')
            ->join('provinces', 'cities.province_id', '=', 'provinces.id')
            ->select('provinces.name as province', DB::raw('count(*) as count'))
            ->groupBy('provinces.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Get livestock distribution by species
        $livestockBySpecies = Livestock::join('breeds', 'livestocks.breed_id', '=', 'breeds.id')
            ->join('species', 'breeds.species_id', '=', 'species.id')
            ->select('species.name', DB::raw('count(*) as count'))
            ->groupBy('species.name')
            ->orderBy('count', 'desc')
            ->get();

        // Get all superusers
        $superusers = User::where('is_super_user', true)
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->get();

        return Inertia::render('SuperDashboard', [
            'stats' => $stats,
            'topFarmsByLivestock' => $topFarmsByLivestock,
            'topFarmsByUsers' => $topFarmsByUsers,
            'farmsByRegion' => $farmsByRegion,
            'livestockBySpecies' => $livestockBySpecies,
            'superusers' => $superusers,
        ]);
    }

    public function createSuperUser(Request $request)
    {
        // Check if user is superuser
        if (!auth()->user()->is_super_user) {
            abort(403, 'Access denied. Superuser privileges required.');
        }

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User with this email does not exist.']);
        }

        if ($user->is_super_user) {
            return back()->withErrors(['email' => 'User is already a superuser.']);
        }

        $user->update(['is_super_user' => true]);

        return back()->with('success', "User {$user->name} ({$user->email}) has been granted superuser privileges.");
    }

    public function removeSuperUser(Request $request, User $user)
    {
        // Check if user is superuser
        if (!auth()->user()->is_super_user) {
            abort(403, 'Access denied. Superuser privileges required.');
        }

        // Prevent user from removing themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot remove superuser privileges from yourself.']);
        }

        // Check if user is actually a superuser
        if (!$user->is_super_user) {
            return back()->withErrors(['error' => 'User is not a superuser.']);
        }

        $user->update(['is_super_user' => false]);

        return back()->with('success', "Superuser privileges removed from {$user->name} ({$user->email}).");
    }
}