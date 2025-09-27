<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Farm;
use App\Models\FarmInvite;
use App\Models\Livestock;
use App\Models\LivestockMilking;
use App\Models\LivestockWeight;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\HasCurrentFarm;

class HomeController extends Controller
{
    use HasCurrentFarm;
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if($this->hasCurrentFarm()) {
            return redirect()->route('dashboard');
        }
        // Get farm invitations for current user even when no current farm
        $farmInvites = FarmInvite::where('email', Auth::user()->email)
            ->with('farm')
            ->get();
        $data = ['farmInvites' => $farmInvites];
        return Inertia::render('home/Home', $data);
    }
    
    public function farmLogout(Request $request)
    {
        auth()->user()->update(['current_farm_id' => null]);
        return redirect()->route('home')->with('success', 'Anda telah logout ke peternakan. Pilih peternakan untuk login kembali.');
    }

    public function dashboard()
    {
        $currentFarmId = Auth::user()->current_farm_id;
        
        if (!$currentFarmId) {
            // Get farm invitations for current user even when no current farm
            $farmInvites = FarmInvite::where('email', Auth::user()->email)
                ->with('farm')
                ->get();
                
            $data = [
                'totalLivestock' => 0,
                'todayMilkProduction' => 0,
                'totalMilkProduction' => 0,
                'averageWeight' => 0,
                'milkProductionTrend' => null,
                'weightTrend' => null,
                'notification' => [
                    'emoji' => '🏠',
                    'message' => 'Belum ada data ternak. Silakan tambahkan data ternak terlebih dahulu untuk melihat perkembangan populasi dan produksi susu.'
                ],
                'farmInvites' => $farmInvites,
            ];
            return Inertia::render('home/Dashboard', $data);
        }

        // Get all active livestock in current farm (excluding ended livestock)
        $livestocks = Livestock::where('farm_id', $currentFarmId)
            ->whereDoesntHave('endings')
            ->with(['milkings', 'weights'])
            ->get();

        // Calculate today's milk production
        $today = Carbon::today();
        $todayMilkProduction = LivestockMilking::whereHas('livestock', function($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId);
        })
        ->whereDate('date', $today)
        ->sum('milk_volume');

        // Calculate yesterday's milk production for comparison
        $yesterday = Carbon::yesterday();
        $yesterdayMilkProduction = LivestockMilking::whereHas('livestock', function($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId);
        })
        ->whereDate('date', $yesterday)
        ->sum('milk_volume');

        // Calculate total milk production
        $totalMilkProduction = LivestockMilking::whereHas('livestock', function($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId);
        })->sum('milk_volume');

        // Calculate milk production trend
        $milkTrend = null;
        if ($yesterdayMilkProduction > 0) {
            $milkTrend = (($todayMilkProduction - $yesterdayMilkProduction) / $yesterdayMilkProduction) * 100;
        } elseif ($todayMilkProduction > 0 && $yesterdayMilkProduction == 0) {
            // If there's production today but none yesterday, show as 100% increase
            $milkTrend = 100;
        }

        // Calculate average weight of all livestock
        $totalWeight = 0;
        $weightCount = 0;
        foreach ($livestocks as $livestock) {
            $latestWeight = $livestock->weights()->orderByDesc('date')->first();
            if ($latestWeight) {
                $totalWeight += $latestWeight->weight;
                $weightCount++;
            } elseif ($livestock->weight) {
                $totalWeight += $livestock->weight;
                $weightCount++;
            }
        }
        $averageWeight = $weightCount > 0 ? round($totalWeight / $weightCount, 2) : 0;

        // Calculate weight trend (this week vs last week)
        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        
        $thisWeekWeights = LivestockWeight::whereHas('livestock', function($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId);
        })
        ->where('date', '>=', $thisWeekStart)
        ->avg('weight');

        $lastWeekWeights = LivestockWeight::whereHas('livestock', function($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId);
        })
        ->where('date', '>=', $lastWeekStart)
        ->where('date', '<', $thisWeekStart)
        ->avg('weight');

        $weightTrend = null;
        if ($lastWeekWeights > 0 && $thisWeekWeights > 0) {
            $weightTrend = (($thisWeekWeights - $lastWeekWeights) / $lastWeekWeights) * 100;
        } elseif ($thisWeekWeights > 0 && ($lastWeekWeights == 0 || $lastWeekWeights === null)) {
            // If there's weight data this week but none last week, show as positive trend
            $weightTrend = 100;
        }

        // Generate dynamic notification
        $notification = $this->generateNotification($todayMilkProduction, $milkTrend, $weightTrend, $livestocks->count());

        // Get farm invitations for current user
        $farmInvites = FarmInvite::where('email', Auth::user()->email)
            ->with('farm')
            ->get();

        $data = [
            'totalLivestock' => $livestocks->count(),
            'todayMilkProduction' => round($todayMilkProduction, 2),
            'totalMilkProduction' => round($totalMilkProduction, 2),
            'averageWeight' => $averageWeight,
            'milkProductionTrend' => $milkTrend !== null ? round($milkTrend, 1) : null,
            'weightTrend' => $weightTrend !== null ? round($weightTrend, 1) : null,
            'notification' => $notification,
            'farmInvites' => $farmInvites
        ];

        return Inertia::render('home/Dashboard', $data);
    }

    private function generateNotification($todayMilk, $milkTrend, $weightTrend, $totalLivestock)
    {
        // Milk price per liter (hard coded as requested)
        $milkPrice = 18000;

        if ($totalLivestock === 0) {
            return [
                'emoji' => '😐',
                'message' => 'Tidak ada aktivitas untuk hari ini'
            ];
        }

        // If no milk production data available
        if ($todayMilk <= 0) {
            return [
                'emoji' => '😐',
                'message' => 'Tidak ada aktivitas untuk hari ini'
            ];
        }

        // Calculate revenue from today's milk production
        $todayRevenue = $todayMilk * $milkPrice;

        // Simple profit calculation based on milk trend
        // If trend is positive, assume profit; if negative or neutral, assume loss
        if ($milkTrend !== null && $milkTrend > 0) {
            // Profit scenario
            $emoji = '😊';
            $profitAmount = number_format($todayRevenue / 2, 0, ',', '.'); // Example: half of revenue as profit
            $message = "Pertumbuhan peternakan mu dari seluruh populasi hari ini *UNTUNG Rp.{$profitAmount},-*\n\n_Catatan :_\nHarga susu hari ini = *Rp.18.000,-*";
        } else {
            // Loss scenario (when trend is negative, neutral, or no trend data)
            $emoji = '☹️';
            $lossAmount = number_format($todayRevenue / 4, 0, ',', '.'); // Example: quarter of revenue as loss
            $message = "Pertumbuhan peternakan mu dari seluruh populasi hari ini *RUGI Rp.{$lossAmount},-*\n\n_Catatan :_\nHarga susu hari ini = *Rp.18.000,-*";
        }

        return [
            'emoji' => $emoji,
            'message' => $message
        ];
    }
}
