<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\User;
use App\Models\Livestock;
use App\Models\Herd;
use App\Models\Ration;
use App\Models\Feed;
use App\Models\HerdFeeding;
use App\Models\LivestockWeight;
use App\Models\LivestockMilking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

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
        if ($selectedFarm) {
            // Farm-specific statistics
            $stats = [
                // Farm statistics (single farm)
                'total_farms' => 1,
                'active_farms' => $selectedFarm->users()->exists() ? 1 : 0,
                'farms_with_livestock' => $selectedFarm->livestocks()->exists() ? 1 : 0,
                
                // User statistics (farm members)
                'total_users' => $selectedFarm->users()->count(),
                'super_users' => $selectedFarm->users()->where('is_super_user', true)->count(),
                'users_with_farms' => $selectedFarm->users()->count(), // All users in this farm have farms
                
                // Livestock statistics (farm-specific)
                'total_livestock' => $selectedFarm->livestocks()->count(),
                'male_livestock' => $selectedFarm->livestocks()->where('sex', 'M')->count(),
                'female_livestock' => $selectedFarm->livestocks()->where('sex', 'F')->count(),
                'livestock_with_photos' => $selectedFarm->livestocks()
                    ->whereNotNull('photo')
                    ->whereRaw("photo::text != '[]'")
                    ->whereRaw("photo::text != 'null'")
                    ->whereRaw("json_array_length(photo::json) > 0")
                    ->count(),
                
                // Herd statistics (farm-specific)
                'total_herds' => $selectedFarm->herds()->count(),
                'herds_with_livestock' => $selectedFarm->herds()->whereHas('livestocks')->count(),
                
                // Feed and Ration statistics (farm-specific)
                'total_rations' => $selectedFarm->rations()->count(),
                'total_feeds' => Feed::count(), // Feeds are global
                
                // Recent activity (farm-specific)
                'farms_created_last_30_days' => 0, // Not applicable for single farm
                'users_registered_last_30_days' => User::where('created_at', '>=', now()->subDays(30))
                    ->whereHas('farms', function($query) use ($selectedFarm) {
                        $query->where('farm_id', $selectedFarm->id);
                    })->count(),
                'livestock_added_last_30_days' => $selectedFarm->livestocks()
                    ->where('created_at', '>=', now()->subDays(30))->count(),
            ];
        } else {
            // Global statistics (all farms)
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
        }

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

        // Get all farms for the dropdown
        $allFarms = Farm::select('id', 'name', 'address')
            ->orderBy('name')
            ->get();

        // Generate chart data for last 12 months
        $chartData = $this->generateChartData($selectedFarm);

        return Inertia::render('SuperDashboard', [
            'stats' => $stats,
            'topFarmsByLivestock' => $topFarmsByLivestock,
            'topFarmsByUsers' => $topFarmsByUsers,
            'farmsByRegion' => $farmsByRegion,
            'livestockBySpecies' => $livestockBySpecies,
            'superusers' => $superusers,
            'allFarms' => $allFarms,
            'selectedFarm' => $selectedFarm,
            'selectedFarmId' => $selectedFarmId ?? 'all',
            'chartData' => $chartData,
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

    private function generateChartData($selectedFarm = null)
    {
        // Generate labels for last 12 months
        $months = [];
        $currentMonth = Carbon::now();
        
        for ($i = 11; $i >= 0; $i--) {
            $months[] = $currentMonth->copy()->subMonths($i)->format('M Y');
        }

        // Activity counts for feeding, weighing, and milking
        $feedingData = [];
        $weighingData = [];
        $milkingData = [];
        $livestockData = [];

        for ($i = 11; $i >= 0; $i--) {
            $startDate = $currentMonth->copy()->subMonths($i)->startOfMonth();
            $endDate = $currentMonth->copy()->subMonths($i)->endOfMonth();

            if ($selectedFarm) {
                // Farm-specific data
                $feedingCount = HerdFeeding::whereHas('herd', function($query) use ($selectedFarm) {
                    $query->where('farm_id', $selectedFarm->id);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

                $weighingCount = LivestockWeight::whereHas('livestock', function($query) use ($selectedFarm) {
                    $query->where('farm_id', $selectedFarm->id);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

                $milkingCount = LivestockMilking::whereHas('livestock', function($query) use ($selectedFarm) {
                    $query->where('farm_id', $selectedFarm->id);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

                $livestockCount = $selectedFarm->livestocks()
                    ->where('created_at', '<=', $endDate)
                    ->count();
            } else {
                // Global data
                $feedingCount = HerdFeeding::whereBetween('date', [$startDate, $endDate])->count();
                $weighingCount = LivestockWeight::whereBetween('date', [$startDate, $endDate])->count();
                $milkingCount = LivestockMilking::whereBetween('date', [$startDate, $endDate])->count();
                $livestockCount = Livestock::where('created_at', '<=', $endDate)->count();
            }

            $feedingData[] = $feedingCount;
            $weighingData[] = $weighingCount;
            $milkingData[] = $milkingCount;
            $livestockData[] = $livestockCount;
        }

        return [
            'months' => $months,
            'activities' => [
                'feeding' => $feedingData,
                'weighing' => $weighingData,
                'milking' => $milkingData,
            ],
            'livestock' => $livestockData,
        ];
    }
}