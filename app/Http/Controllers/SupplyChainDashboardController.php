<?php

namespace App\Http\Controllers;

use App\Models\MilkBatch;
use App\Models\CheeseProduction;
use App\Models\MilkPayment;
use App\Traits\HasCurrentFarm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplyChainDashboardController extends Controller
{
    use HasCurrentFarm;

    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return Inertia::render('SupplyChain/Dashboard', [
                'stats' => null,
                'recentBatches' => [],
                'agingCheese' => [],
                'alerts' => [],
            ]);
        }

        // Get date range (default: last 30 days)
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(30);

        // Overall Stats
        $stats = [
            // Today's collections
            'collections_today' => MilkBatch::where('farm_id', $currentFarmId)
                ->whereDate('collection_date', Carbon::today())
                ->count(),
            'volume_today' => MilkBatch::where('farm_id', $currentFarmId)
                ->whereDate('collection_date', Carbon::today())
                ->sum('total_volume'),

            // In Transit
            'in_transit' => MilkBatch::where('farm_id', $currentFarmId)
                ->where('status', 'in_transit')
                ->count(),

            // Awaiting QC
            'awaiting_qc' => MilkBatch::where('farm_id', $currentFarmId)
                ->whereIn('status', ['collected', 'received'])
                ->count(),

            // In Production
            'in_production' => CheeseProduction::where('farm_id', $currentFarmId)
                ->whereIn('status', ['in_production', 'aging'])
                ->count(),

            // Monthly volume trend
            'monthly_volume' => MilkBatch::where('farm_id', $currentFarmId)
                ->whereMonth('collection_date', Carbon::now()->month)
                ->whereYear('collection_date', Carbon::now()->year)
                ->sum('total_volume'),

            // Grade distribution (last 30 days)
            'grade_a_percentage' => $this->calculateGradePercentage($currentFarmId, 'A', $startDate, $endDate),
            'grade_b_percentage' => $this->calculateGradePercentage($currentFarmId, 'B', $startDate, $endDate),
            'grade_c_percentage' => $this->calculateGradePercentage($currentFarmId, 'C', $startDate, $endDate),

            // Cheese production
            'aging_cheese_count' => CheeseProduction::where('farm_id', $currentFarmId)
                ->where('status', 'aging')
                ->count(),

            // Pending payments
            'pending_payments' => MilkPayment::where('farm_id', $currentFarmId)
                ->whereIn('status', ['draft', 'approved'])
                ->sum('net_amount'),
        ];

        // Recent batches (last 10)
        $recentBatches = MilkBatch::where('farm_id', $currentFarmId)
            ->with(['collectedBy:id,name'])
            ->orderBy('collection_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Aging cheese with countdown
        $agingCheese = CheeseProduction::where('farm_id', $currentFarmId)
            ->where('status', 'aging')
            ->whereNotNull('aging_start_date')
            ->whereNotNull('aging_target_days')
            ->orderBy('aging_start_date', 'asc')
            ->get()
            ->map(function ($prod) {
                $daysAged = Carbon::parse($prod->aging_start_date)->diffInDays(Carbon::now());
                $daysRemaining = max(0, $prod->aging_target_days - $daysAged);
                $progress = $prod->aging_target_days > 0 ? min(100, ($daysAged / $prod->aging_target_days) * 100) : 0;

                return [
                    'id' => $prod->id,
                    'batch_code' => $prod->batch_code,
                    'cheese_type' => $prod->cheese_type,
                    'aging_start_date' => $prod->aging_start_date,
                    'days_aged' => $daysAged,
                    'days_remaining' => $daysRemaining,
                    'target_days' => $prod->aging_target_days,
                    'progress' => round($progress, 1),
                ];
            });

        // Alerts
        $alerts = $this->generateAlerts($currentFarmId);

        return Inertia::render('SupplyChain/Dashboard', [
            'stats' => $stats,
            'recentBatches' => $recentBatches,
            'agingCheese' => $agingCheese,
            'alerts' => $alerts,
        ]);
    }

    private function calculateGradePercentage(string $farmId, string $grade, $startDate, $endDate): float
    {
        $totalVolume = MilkBatch::where('farm_id', $farmId)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->whereNotNull('quality_grade')
            ->sum('total_volume');

        if ($totalVolume == 0) {
            return 0;
        }

        $gradeVolume = MilkBatch::where('farm_id', $farmId)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->where('quality_grade', $grade)
            ->sum('total_volume');

        return round(($gradeVolume / $totalVolume) * 100, 1);
    }

    private function generateAlerts(string $farmId): array
    {
        $alerts = [];

        // Alert 1: Low quality batches (rejected in last 7 days)
        $rejectedCount = MilkBatch::where('farm_id', $farmId)
            ->where('status', 'rejected')
            ->where('collection_date', '>=', Carbon::now()->subDays(7))
            ->count();

        if ($rejectedCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Batch Ditolak',
                'message' => "{$rejectedCount} batch susu ditolak dalam 7 hari terakhir. Periksa kualitas.",
            ];
        }

        // Alert 2: Delayed collections (in transit > 6 hours)
        $delayedBatches = MilkBatch::where('farm_id', $farmId)
            ->where('status', 'in_transit')
            ->where('collected_at', '<', Carbon::now()->subHours(6))
            ->count();

        if ($delayedBatches > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Pengiriman Tertunda',
                'message' => "{$delayedBatches} batch dalam perjalanan lebih dari 6 jam. Periksa cold chain.",
            ];
        }

        // Alert 3: Cheese ready for completion
        $readyCheeseCount = CheeseProduction::where('farm_id', $farmId)
            ->where('status', 'aging')
            ->whereNotNull('aging_start_date')
            ->whereNotNull('aging_target_days')
            ->whereRaw('aging_start_date + (aging_target_days || \' days\')::INTERVAL <= ?', [Carbon::now()])
            ->count();

        if ($readyCheeseCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Aging Selesai',
                'message' => "{$readyCheeseCount} batch keju siap untuk diselesaikan.",
            ];
        }

        // Alert 4: Pending payments
        $pendingPaymentsCount = MilkPayment::where('farm_id', $farmId)
            ->whereIn('status', ['draft', 'approved'])
            ->count();

        if ($pendingPaymentsCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pembayaran Pending',
                'message' => "Ada {$pendingPaymentsCount} pembayaran menunggu diproses.",
            ];
        }

        return $alerts;
    }
}
