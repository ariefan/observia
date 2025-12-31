<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Farm;
use App\Models\FarmInvite;
use App\Models\Livestock;
use App\Models\LivestockMilking;
use App\Models\LivestockWeight;
use App\Models\HerdFeeding;
use App\Models\Herd;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function dashboard(Request $request)
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

        // Get active livestock IDs for this farm (single query, reused throughout)
        $activeLivestockIds = Livestock::where('farm_id', $currentFarmId)
            ->whereDoesntHave('endings')
            ->pluck('id');

        $totalLivestock = $activeLivestockIds->count();

        // Get herd IDs for this farm (for feed queries)
        $farmHerdIds = Herd::where('farm_id', $currentFarmId)->pluck('id');

        // Handle date/month filtering
        $selectedDate = $request->input('date');
        $selectedMonth = $request->input('month');

        // Default to current month if no parameters provided
        if (!$selectedDate && !$selectedMonth) {
            $selectedMonth = Carbon::now()->format('Y-m');
        }

        // Determine date ranges based on filter
        if ($selectedMonth) {
            [$year, $month] = explode('-', $selectedMonth);
            $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
            $currentEnd = Carbon::create($year, $month, 1)->endOfMonth();
            $previousStart = $currentStart->copy()->subMonth()->startOfMonth();
            $previousEnd = $currentStart->copy()->subMonth()->endOfMonth();
            $isMonthly = true;
        } elseif ($selectedDate) {
            $currentStart = Carbon::parse($selectedDate)->startOfDay();
            $currentEnd = Carbon::parse($selectedDate)->endOfDay();
            $previousStart = Carbon::parse($selectedDate)->subDay()->startOfDay();
            $previousEnd = Carbon::parse($selectedDate)->subDay()->endOfDay();
            $isMonthly = false;
        } else {
            $currentStart = Carbon::today()->startOfDay();
            $currentEnd = Carbon::today()->endOfDay();
            $previousStart = Carbon::yesterday()->startOfDay();
            $previousEnd = Carbon::yesterday()->endOfDay();
            $isMonthly = false;
        }

        // Single optimized query for milk production stats
        $milkStats = LivestockMilking::whereIn('livestock_id', $activeLivestockIds)
            ->selectRaw("
                SUM(CASE WHEN date BETWEEN ? AND ? THEN milk_volume ELSE 0 END) as current_production,
                SUM(CASE WHEN date BETWEEN ? AND ? THEN milk_volume ELSE 0 END) as previous_production,
                SUM(milk_volume) as total_production
            ", [$currentStart, $currentEnd, $previousStart, $previousEnd])
            ->first();

        $todayMilkProduction = $milkStats->current_production ?? 0;
        $yesterdayMilkProduction = $milkStats->previous_production ?? 0;
        $totalMilkProduction = $milkStats->total_production ?? 0;

        // Calculate milk production trend
        $milkTrend = null;
        if ($yesterdayMilkProduction > 0) {
            $milkTrend = (($todayMilkProduction - $yesterdayMilkProduction) / $yesterdayMilkProduction) * 100;
        } elseif ($todayMilkProduction > 0) {
            $milkTrend = 100;
        }

        // Optimized average weight calculation using subquery for latest weights
        $avgWeightResult = DB::table('livestock_weights as lw1')
            ->join(DB::raw('(SELECT livestock_id, MAX(date) as max_date FROM livestock_weights GROUP BY livestock_id) as lw2'), function($join) {
                $join->on('lw1.livestock_id', '=', 'lw2.livestock_id')
                     ->on('lw1.date', '=', 'lw2.max_date');
            })
            ->whereIn('lw1.livestock_id', $activeLivestockIds)
            ->avg('lw1.weight');

        // Fallback to initial weights if no weight records
        if (!$avgWeightResult) {
            $avgWeightResult = Livestock::whereIn('id', $activeLivestockIds)
                ->whereNotNull('weight')
                ->avg('weight');
        }
        $averageWeight = round($avgWeightResult ?? 0, 2);

        // Optimized weight trend calculation (this week vs last week)
        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();

        $weightTrendStats = LivestockWeight::whereIn('livestock_id', $activeLivestockIds)
            ->selectRaw("
                AVG(CASE WHEN date >= ? THEN weight END) as this_week_avg,
                AVG(CASE WHEN date >= ? AND date < ? THEN weight END) as last_week_avg
            ", [$thisWeekStart, $lastWeekStart, $thisWeekStart])
            ->first();

        $thisWeekWeights = $weightTrendStats->this_week_avg;
        $lastWeekWeights = $weightTrendStats->last_week_avg;

        $weightTrend = null;
        if ($lastWeekWeights > 0 && $thisWeekWeights > 0) {
            $weightTrend = (($thisWeekWeights - $lastWeekWeights) / $lastWeekWeights) * 100;
        } elseif ($thisWeekWeights > 0) {
            $weightTrend = 100;
        }

        // Optimized feed amount calculation
        $feedStats = HerdFeeding::whereIn('herd_id', $farmHerdIds)
            ->selectRaw("
                SUM(CASE WHEN date BETWEEN ? AND ? THEN quantity ELSE 0 END) as current_feed,
                SUM(CASE WHEN date BETWEEN ? AND ? THEN quantity ELSE 0 END) as previous_feed
            ", [$currentStart, $currentEnd, $previousStart, $previousEnd])
            ->first();

        $todayFeedAmount = $feedStats->current_feed ?? 0;
        $yesterdayFeedAmount = $feedStats->previous_feed ?? 0;

        // Calculate FCR (Feed Conversion Ratio)
        $todayFCR = ($todayMilkProduction > 0) ? round($todayFeedAmount / $todayMilkProduction, 2) : 0;
        $yesterdayFCR = ($yesterdayMilkProduction > 0) ? round($yesterdayFeedAmount / $yesterdayMilkProduction, 2) : 0;

        // Generate dynamic notification
        $notification = $this->generateNotification($todayMilkProduction, $milkTrend, $weightTrend, $totalLivestock);

        // Get farm invitations for current user
        $farmInvites = FarmInvite::where('email', Auth::user()->email)
            ->with('farm')
            ->get();

        $data = [
            'totalLivestock' => $totalLivestock,
            'todayMilkProduction' => round($todayMilkProduction, 2),
            'totalMilkProduction' => round($totalMilkProduction, 2),
            'averageWeight' => $averageWeight,
            'milkProductionTrend' => $milkTrend !== null ? round($milkTrend, 1) : null,
            'weightTrend' => $weightTrend !== null ? round($weightTrend, 1) : null,
            'todayFCR' => $todayFCR,
            'yesterdayFCR' => $yesterdayFCR,
            'todayFeedAmount' => round($todayFeedAmount, 2),
            'yesterdayFeedAmount' => round($yesterdayFeedAmount, 2),
            'yesterdayMilkProduction' => round($yesterdayMilkProduction, 2),
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
            $message = "Pertumbuhan peternakan mu dari seluruh populasi hari ini UNTUNG Rp.{$profitAmount},-\n\nCatatan:\nHarga susu hari ini = Rp.18.000,-";
        } else {
            // Loss scenario (when trend is negative, neutral, or no trend data)
            $emoji = '☹️';
            $lossAmount = number_format($todayRevenue / 4, 0, ',', '.'); // Example: quarter of revenue as loss
            $message = "Pertumbuhan peternakan mu dari seluruh populasi hari ini RUGI Rp.{$lossAmount},-\n\nCatatan:\nHarga susu hari ini = Rp.18.000,-";
        }

        return [
            'emoji' => $emoji,
            'message' => $message
        ];
    }
}
