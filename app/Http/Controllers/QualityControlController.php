<?php

namespace App\Http\Controllers;

use App\Models\MilkBatch;
use App\Models\Setting;
use App\Services\MilkBatchService;
use App\Traits\HasCurrentFarm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QualityControlController extends Controller
{
    use HasCurrentFarm;

    protected $milkBatchService;

    public function __construct(MilkBatchService $milkBatchService)
    {
        $this->milkBatchService = $milkBatchService;
    }

    /**
     * QC Dashboard - shows batches needing receiving and testing.
     */
    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return Inertia::render('QualityControl/Dashboard', [
                'toReceive' => ['data' => [], 'total' => 0],
                'awaitingTest' => ['data' => [], 'total' => 0],
                'recentlyTested' => ['data' => [], 'total' => 0],
                'stats' => null,
            ]);
        }

        // Batches to receive (in_transit or collected)
        $toReceive = MilkBatch::with(['farm:id,name', 'collectedBy:id,name'])
            ->where('farm_id', $currentFarmId)
            ->whereIn('status', ['collected', 'in_transit'])
            ->orderBy('collection_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(10, ['*'], 'receive_page');

        // Batches awaiting quality test (received but not tested)
        $awaitingTest = MilkBatch::with(['farm:id,name', 'receivedBy:id,name'])
            ->where('farm_id', $currentFarmId)
            ->where('status', 'received')
            ->orderBy('received_at', 'asc')
            ->paginate(10, ['*'], 'test_page');

        // Recently tested batches
        $recentlyTested = MilkBatch::with(['farm:id,name', 'qualityTestedBy:id,name'])
            ->where('farm_id', $currentFarmId)
            ->whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('quality_tested_at')
            ->orderBy('quality_tested_at', 'desc')
            ->paginate(10, ['*'], 'tested_page');

        // Calculate stats
        $stats = $this->calculateQCStats($currentFarmId);

        // Get quality standards for reference
        $qualityStandards = $this->getQualityStandards();

        return Inertia::render('QualityControl/Dashboard', [
            'toReceive' => $toReceive,
            'awaitingTest' => $awaitingTest,
            'recentlyTested' => $recentlyTested,
            'stats' => $stats,
            'qualityStandards' => $qualityStandards,
        ]);
    }

    /**
     * Show receiving form for a specific batch.
     */
    public function receiveForm(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::with(['farm:id,name', 'collectedBy:id,name'])
            ->findOrFail($id);

        // Verify ownership
        if ($batch->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Check if batch can be received
        if (!in_array($batch->status, ['collected', 'in_transit'])) {
            return redirect()->route('quality-control.index')
                ->withErrors(['error' => 'Batch tidak dapat diterima pada status: ' . $batch->status]);
        }

        $temperatureRange = $this->getTemperatureRange();

        return Inertia::render('QualityControl/Receive', [
            'batch' => $batch,
            'temperatureRange' => $temperatureRange,
        ]);
    }

    /**
     * Process receiving of a batch.
     */
    public function receive(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::findOrFail($id);

        if ($batch->farm_id !== $currentFarmId) {
            abort(403);
        }

        $validated = $request->validate([
            'transport_temp_delivery' => 'required|numeric|min:0|max:15',
            'transport_duration_minutes' => 'nullable|integer|min:0',
            'visual_check' => 'required|in:normal,abnormal,foamy,discolored',
            'smell_check' => 'required|in:normal,sour,abnormal',
            'transport_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $batch = $this->milkBatchService->updateReceiving($batch, $validated);

            return redirect()->route('quality-control.index')
                ->with('success', 'Penerimaan batch berhasil dicatat. Status: ' . ($batch->status === 'rejected' ? 'Ditolak' : 'Diterima'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show quality test form for a specific batch.
     */
    public function testForm(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::with(['farm:id,name', 'receivedBy:id,name'])
            ->findOrFail($id);

        // Verify ownership
        if ($batch->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Check if batch can be tested
        if ($batch->status !== 'received') {
            return redirect()->route('quality-control.index')
                ->withErrors(['error' => 'Batch harus diterima terlebih dahulu sebelum diuji. Status saat ini: ' . $batch->status]);
        }

        $qualityStandards = $this->getQualityStandards();

        return Inertia::render('QualityControl/TestForm', [
            'batch' => $batch,
            'qualityStandards' => $qualityStandards,
        ]);
    }

    /**
     * Store quality test results.
     */
    public function storeTest(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::findOrFail($id);

        if ($batch->farm_id !== $currentFarmId) {
            abort(403);
        }

        $validated = $request->validate([
            'quality_data' => 'required|array',
            'quality_data.pH' => 'required|numeric|min:0|max:14',
            'quality_data.fat_percentage' => 'required|numeric|min:0|max:100',
            'quality_data.protein_percentage' => 'nullable|numeric|min:0|max:100',
            'quality_data.bacteria_count' => 'required|integer|min:0',
            'quality_data.temperature' => 'required|numeric|min:0|max:20',
            'quality_data.snf_percentage' => 'nullable|numeric|min:0|max:100',
            'quality_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $batch = $this->milkBatchService->updateQualityTest($batch, $validated);

            return redirect()->route('quality-control.index')
                ->with('success', "Uji kualitas selesai. Grade: {$batch->quality_grade}, Status: " . ($batch->status === 'approved' ? 'Disetujui' : 'Ditolak'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show grading history.
     */
    public function gradingHistory(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return Inertia::render('QualityControl/GradingHistory', [
                'batches' => ['data' => [], 'total' => 0],
                'gradeStats' => null,
                'filters' => [],
            ]);
        }

        $query = MilkBatch::with(['farm:id,name', 'qualityTestedBy:id,name'])
            ->where('farm_id', $currentFarmId)
            ->whereNotNull('quality_grade')
            ->whereNotNull('quality_tested_at');

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('quality_grade', $request->get('grade'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('quality_tested_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('quality_tested_at', '<=', $request->get('date_to'));
        }

        $batches = $query->orderBy('quality_tested_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Calculate grade distribution stats
        $gradeStats = $this->calculateGradeStats($currentFarmId);

        return Inertia::render('QualityControl/GradingHistory', [
            'batches' => $batches,
            'gradeStats' => $gradeStats,
            'filters' => [
                'grade' => $request->get('grade'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ],
        ]);
    }

    /**
     * Calculate QC statistics.
     */
    private function calculateQCStats($farmId)
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);

        return [
            'pending_receive' => MilkBatch::where('farm_id', $farmId)
                ->whereIn('status', ['collected', 'in_transit'])
                ->count(),
            'pending_test' => MilkBatch::where('farm_id', $farmId)
                ->where('status', 'received')
                ->count(),
            'tested_today' => MilkBatch::where('farm_id', $farmId)
                ->whereDate('quality_tested_at', $today)
                ->count(),
            'tested_this_week' => MilkBatch::where('farm_id', $farmId)
                ->where('quality_tested_at', '>=', $weekAgo)
                ->count(),
            'avg_grade_this_week' => $this->calculateAverageGrade($farmId, $weekAgo),
        ];
    }

    /**
     * Calculate average grade (numeric).
     */
    private function calculateAverageGrade($farmId, $since)
    {
        $grades = MilkBatch::where('farm_id', $farmId)
            ->where('quality_tested_at', '>=', $since)
            ->whereNotNull('quality_grade')
            ->pluck('quality_grade');

        if ($grades->isEmpty()) {
            return null;
        }

        $gradeValues = $grades->map(function ($grade) {
            return match ($grade) {
                'A' => 4,
                'B' => 3,
                'C' => 2,
                'Reject' => 1,
                default => 0,
            };
        });

        $avg = $gradeValues->average();

        if ($avg >= 3.5) return 'A';
        if ($avg >= 2.5) return 'B';
        if ($avg >= 1.5) return 'C';
        return 'Reject';
    }

    /**
     * Calculate grade distribution statistics.
     */
    private function calculateGradeStats($farmId)
    {
        $total = MilkBatch::where('farm_id', $farmId)
            ->whereNotNull('quality_grade')
            ->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'grade_a_count' => 0,
                'grade_b_count' => 0,
                'grade_c_count' => 0,
                'reject_count' => 0,
                'grade_a_percentage' => 0,
                'grade_b_percentage' => 0,
                'grade_c_percentage' => 0,
                'reject_percentage' => 0,
            ];
        }

        $gradeA = MilkBatch::where('farm_id', $farmId)->where('quality_grade', 'A')->count();
        $gradeB = MilkBatch::where('farm_id', $farmId)->where('quality_grade', 'B')->count();
        $gradeC = MilkBatch::where('farm_id', $farmId)->where('quality_grade', 'C')->count();
        $reject = MilkBatch::where('farm_id', $farmId)->where('quality_grade', 'Reject')->count();

        return [
            'total' => $total,
            'grade_a_count' => $gradeA,
            'grade_b_count' => $gradeB,
            'grade_c_count' => $gradeC,
            'reject_count' => $reject,
            'grade_a_percentage' => round(($gradeA / $total) * 100, 2),
            'grade_b_percentage' => round(($gradeB / $total) * 100, 2),
            'grade_c_percentage' => round(($gradeC / $total) * 100, 2),
            'reject_percentage' => round(($reject / $total) * 100, 2),
        ];
    }

    /**
     * Get quality standards from settings.
     */
    private function getQualityStandards(): array
    {
        $setting = Setting::where('key', 'milk_quality_standards')->first();

        if (!$setting) {
            return [
                'grade_a' => [
                    'pH_min' => 6.6,
                    'pH_max' => 6.8,
                    'fat_min' => 3.5,
                    'bacteria_max' => 100000,
                ],
                'grade_b' => [
                    'pH_min' => 6.5,
                    'pH_max' => 6.9,
                    'fat_min' => 3.0,
                    'bacteria_max' => 500000,
                ],
                'grade_c' => [
                    'pH_min' => 6.4,
                    'pH_max' => 7.0,
                    'fat_min' => 2.5,
                    'bacteria_max' => 1000000,
                ],
            ];
        }

        return json_decode($setting->value, true);
    }

    /**
     * Get temperature range settings.
     */
    private function getTemperatureRange(): array
    {
        $setting = Setting::where('key', 'milk_temperature_range')->first();

        if (!$setting) {
            return [
                'pickup_min' => 4,
                'pickup_max' => 7,
                'warning_threshold' => 10,
            ];
        }

        return json_decode($setting->value, true);
    }
}
