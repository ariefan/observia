<?php

namespace App\Http\Controllers;

use App\Models\MilkBatch;
use App\Models\Farm;
use App\Models\LivestockMilking;
use App\Services\MilkBatchService;
use App\Traits\HasCurrentFarm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MilkBatchController extends Controller
{
    use HasCurrentFarm;

    protected $milkBatchService;

    public function __construct(MilkBatchService $milkBatchService)
    {
        $this->milkBatchService = $milkBatchService;
    }

    /**
     * Display a listing of milk batches.
     */
    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return Inertia::render('MilkCollection/Index', [
                'batches' => ['data' => [], 'total' => 0],
                'filters' => [],
                'stats' => null,
            ]);
        }

        $query = MilkBatch::with([
            'farm:id,name',
            'collectedBy:id,name',
            'receivedBy:id,name',
            'qualityTestedBy:id,name'
        ])->where('farm_id', $currentFarmId);

        // Search by batch code
        if ($request->filled('search')) {
            $search = escapeLike($request->get('search'));
            $query->where('batch_code', 'ILIKE', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by quality grade
        if ($request->filled('grade')) {
            $query->where('quality_grade', $request->get('grade'));
        }

        // Filter by collection date range
        if ($request->filled('date_from')) {
            $query->where('collection_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('collection_date', '<=', $request->get('date_to'));
        }

        // Filter by session
        if ($request->filled('session')) {
            $query->where('session', $request->get('session'));
        }

        $batches = $query->orderBy('collection_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calculate stats for current month
        $stats = $this->calculateStats($currentFarmId);

        return Inertia::render('MilkCollection/Index', [
            'batches' => $batches,
            'filters' => [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'grade' => $request->get('grade'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'session' => $request->get('session'),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new milk batch.
     */
    public function create(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return redirect()->back()->withErrors(['farm' => 'No farm selected.']);
        }

        // Get available milking records that haven't been batched yet
        $collectionDate = $request->get('collection_date', Carbon::today()->format('Y-m-d'));
        $session = $request->get('session', 'morning');

        $availableMilkings = LivestockMilking::with(['livestock:id,name,tag_id,farm_id'])
            ->whereHas('livestock', function ($query) use ($currentFarmId) {
                $query->where('farm_id', $currentFarmId);
            })
            ->whereDate('date', $collectionDate)
            ->where('session', $session)
            // Not already in a batch (check JSON array)
            ->whereRaw("id NOT IN (
                SELECT DISTINCT jsonb_array_elements_text(source_livestock_milking_ids::jsonb)::bigint
                FROM milk_batches
                WHERE source_livestock_milking_ids IS NOT NULL
            )")
            ->orderBy('time')
            ->get();

        $estimatedVolume = $availableMilkings->sum('milk_volume');

        return Inertia::render('MilkCollection/Create', [
            'availableMilkings' => $availableMilkings,
            'estimatedVolume' => $estimatedVolume,
            'collectionDate' => $collectionDate,
            'session' => $session,
        ]);
    }

    /**
     * Store a newly created milk batch.
     */
    public function store(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return redirect()->back()->withErrors(['farm' => 'No farm selected.']);
        }

        $validated = $request->validate([
            'collection_date' => 'required|date',
            'session' => 'required|in:morning,afternoon,evening',
            'source_livestock_milking_ids' => 'required|array|min:1',
            'source_livestock_milking_ids.*' => 'exists:livestock_milkings,id',
            'estimated_volume' => 'required|numeric|min:0',
            'actual_volume' => 'required|numeric|min:0',
            'transport_temp_pickup' => 'required|numeric|min:0|max:15',
            'transport_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $milkBatch = $this->milkBatchService->createBatch(
                $currentFarmId,
                $validated
            );

            return redirect()->route('milk-batches.show', $milkBatch->id)
                ->with('success', 'Batch susu berhasil dibuat dengan kode: ' . $milkBatch->batch_code);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified milk batch.
     */
    public function show(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::with([
            'farm:id,name',
            'collectedBy:id,name',
            'receivedBy:id,name',
            'qualityTestedBy:id,name'
        ])->findOrFail($id);

        // Verify the batch belongs to the current farm
        if ($batch->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Get source milking records for traceability
        $sourceMilkings = [];
        if ($batch->source_livestock_milking_ids) {
            $sourceMilkings = LivestockMilking::with(['livestock:id,name,tag_id'])
                ->whereIn('id', $batch->source_livestock_milking_ids)
                ->get();
        }

        return Inertia::render('MilkCollection/Show', [
            'batch' => $batch,
            'sourceMilkings' => $sourceMilkings,
        ]);
    }

    /**
     * Update milk batch status (receiving workflow).
     */
    public function updateReceiving(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::findOrFail($id);

        // Verify the batch belongs to the current farm
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

            return redirect()->route('milk-batches.show', $batch->id)
                ->with('success', 'Penerimaan batch susu berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update milk batch quality test.
     */
    public function updateQualityTest(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $batch = MilkBatch::findOrFail($id);

        // Verify the batch belongs to the current farm
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

            return redirect()->route('milk-batches.show', $batch->id)
                ->with('success', "Uji kualitas selesai. Grade: {$batch->quality_grade}");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Calculate statistics for dashboard.
     */
    private function calculateStats($farmId)
    {
        $currentMonth = Carbon::now()->startOfMonth();

        return [
            'total_batches_month' => MilkBatch::where('farm_id', $farmId)
                ->where('collection_date', '>=', $currentMonth)
                ->count(),
            'total_volume_month' => MilkBatch::where('farm_id', $farmId)
                ->where('collection_date', '>=', $currentMonth)
                ->sum('total_volume'),
            'grade_a_percentage' => $this->getGradePercentage($farmId, $currentMonth, 'A'),
            'grade_b_percentage' => $this->getGradePercentage($farmId, $currentMonth, 'B'),
            'grade_c_percentage' => $this->getGradePercentage($farmId, $currentMonth, 'C'),
            'rejected_percentage' => $this->getGradePercentage($farmId, $currentMonth, 'Reject'),
        ];
    }

    private function getGradePercentage($farmId, $date, $grade)
    {
        $total = MilkBatch::where('farm_id', $farmId)
            ->where('collection_date', '>=', $date)
            ->whereNotNull('quality_grade')
            ->count();

        if ($total === 0) {
            return 0;
        }

        $gradeCount = MilkBatch::where('farm_id', $farmId)
            ->where('collection_date', '>=', $date)
            ->where('quality_grade', $grade)
            ->count();

        return round(($gradeCount / $total) * 100, 2);
    }
}
