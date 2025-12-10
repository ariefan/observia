<?php

namespace App\Http\Controllers;

use App\Models\CheeseProduction;
use App\Models\MilkBatch;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Services\CheeseProductionService;
use App\Traits\HasCurrentFarm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheeseProductionController extends Controller
{
    use HasCurrentFarm;

    protected $cheeseProductionService;

    public function __construct(CheeseProductionService $cheeseProductionService)
    {
        $this->cheeseProductionService = $cheeseProductionService;
    }

    /**
     * Display a listing of cheese productions.
     */
    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return Inertia::render('CheeseProduction/Index', [
                'productions' => ['data' => [], 'total' => 0],
                'filters' => [],
                'stats' => null,
            ]);
        }

        $query = CheeseProduction::with([
            'farm:id,name',
            'producedBy:id,name',
            'inventoryItem'
        ])->where('farm_id', $currentFarmId);

        // Search by batch code or cheese type
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'ILIKE', "%{$search}%")
                  ->orWhere('cheese_type', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by cheese type
        if ($request->filled('cheese_type')) {
            $query->where('cheese_type', $request->get('cheese_type'));
        }

        // Filter by production date range
        if ($request->filled('date_from')) {
            $query->where('production_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('production_date', '<=', $request->get('date_to'));
        }

        $productions = $query->orderBy('production_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calculate stats
        $stats = $this->calculateStats($currentFarmId);

        // Get available cheese types
        $cheeseTypes = CheeseProduction::where('farm_id', $currentFarmId)
            ->distinct()
            ->pluck('cheese_type');

        return Inertia::render('CheeseProduction/Index', [
            'productions' => $productions,
            'filters' => [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'cheese_type' => $request->get('cheese_type'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ],
            'stats' => $stats,
            'cheeseTypes' => $cheeseTypes,
        ]);
    }

    /**
     * Show the form for creating a new cheese production.
     */
    public function create()
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return redirect()->back()->withErrors(['farm' => 'No farm selected.']);
        }

        // Get approved milk batches available for production
        $availableBatches = MilkBatch::with(['farm:id,name'])
            ->where('farm_id', $currentFarmId)
            ->where('status', 'approved')
            // Not already used in production (check JSON array)
            ->whereRaw("id NOT IN (
                SELECT DISTINCT jsonb_array_elements_text(milk_batch_ids::jsonb)::bigint
                FROM cheese_productions
                WHERE milk_batch_ids IS NOT NULL
            )")
            ->orderBy('quality_grade')
            ->orderBy('collection_date', 'desc')
            ->get();

        // Get cheese types from inventory category "Produk Susu"
        $cheeseCategory = InventoryCategory::where('name', 'ILIKE', '%Produk Susu%')
            ->orWhere('name', 'ILIKE', '%Cheese%')
            ->orWhere('name', 'ILIKE', '%Keju%')
            ->first();

        $cheeseTypes = collect([]);
        if ($cheeseCategory) {
            $cheeseTypes = InventoryItem::where('category_id', $cheeseCategory->id)
                ->where('farm_id', $currentFarmId)
                ->where('is_active', true)
                ->pluck('name');
        }

        // If no cheese types in inventory, provide defaults
        if ($cheeseTypes->isEmpty()) {
            $cheeseTypes = collect(['Wild Eclipse', 'Tomme De Merapi', 'Fresh Mozzarella', 'Aged Cheddar']);
        }

        return Inertia::render('CheeseProduction/Create', [
            'availableBatches' => $availableBatches,
            'cheeseTypes' => $cheeseTypes,
        ]);
    }

    /**
     * Store a newly created cheese production.
     */
    public function store(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();

        if (!$currentFarmId) {
            return redirect()->back()->withErrors(['farm' => 'No farm selected.']);
        }

        $validated = $request->validate([
            'cheese_type' => 'required|string|max:100',
            'production_date' => 'required|date',
            'milk_batch_ids' => 'required|array|min:1',
            'milk_batch_ids.*' => 'exists:milk_batches,id',
            'recipe_notes' => 'nullable|string',
            'starter_culture' => 'nullable|string|max:100',
            'rennet_type' => 'nullable|string|max:50',
            'rennet_amount' => 'nullable|string|max:50',
            'additional_ingredients' => 'nullable|array',
            'process_parameters' => 'nullable|array',
            'cheese_weight_kg' => 'nullable|numeric|min:0',
            'aging_target_days' => 'nullable|integer|min:0',
            'storage_location' => 'nullable|string|max:100',
        ]);

        try {
            $production = $this->cheeseProductionService->createProduction(
                $currentFarmId,
                $validated
            );

            return redirect()->route('cheese-productions.show', $production->id)
                ->with('success', 'Produksi keju berhasil dibuat: ' . $production->batch_code);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified cheese production.
     */
    public function show(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $production = CheeseProduction::with([
            'farm:id,name',
            'producedBy:id,name',
            'inventoryItem.unit'
        ])->findOrFail($id);

        // Verify ownership
        if ($production->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Get source milk batches for traceability
        $sourceBatches = MilkBatch::with(['farm:id,name'])
            ->whereIn('id', $production->milk_batch_ids)
            ->get();

        // Calculate days aged
        $daysAged = null;
        if ($production->aging_start_date) {
            $daysAged = Carbon::parse($production->aging_start_date)->diffInDays(now());
        }

        return Inertia::render('CheeseProduction/Show', [
            'production' => $production,
            'sourceBatches' => $sourceBatches,
            'daysAged' => $daysAged,
        ]);
    }

    /**
     * Show aging tracker for a production.
     */
    public function agingTracker(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $production = CheeseProduction::with(['farm:id,name', 'producedBy:id,name'])
            ->findOrFail($id);

        if ($production->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Calculate days aged
        $daysAged = null;
        $daysRemaining = null;
        if ($production->aging_start_date) {
            $daysAged = Carbon::parse($production->aging_start_date)->diffInDays(now());
            if ($production->aging_target_days) {
                $daysRemaining = $production->aging_target_days - $daysAged;
            }
        }

        return Inertia::render('CheeseProduction/AgingTracker', [
            'production' => $production,
            'daysAged' => $daysAged,
            'daysRemaining' => $daysRemaining,
        ]);
    }

    /**
     * Update aging notes.
     */
    public function updateAgingNote(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $production = CheeseProduction::findOrFail($id);

        if ($production->farm_id !== $currentFarmId) {
            abort(403);
        }

        $validated = $request->validate([
            'check_date' => 'required|date',
            'notes' => 'required|string',
            'weight_kg' => 'nullable|numeric|min:0',
            'photos' => 'nullable|array',
        ]);

        $agingNotes = $production->aging_notes ?? [];
        $agingNotes[] = array_merge($validated, [
            'days_aged' => Carbon::parse($production->aging_start_date)->diffInDays($validated['check_date']),
        ]);

        $production->update(['aging_notes' => $agingNotes]);

        return redirect()->back()
            ->with('success', 'Catatan aging berhasil ditambahkan.');
    }

    /**
     * Complete aging process.
     */
    public function completeAging(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();

        $production = CheeseProduction::findOrFail($id);

        if ($production->farm_id !== $currentFarmId) {
            abort(403);
        }

        try {
            $production = $this->cheeseProductionService->completeAging($production);

            return redirect()->route('cheese-productions.show', $production->id)
                ->with('success', 'Proses aging selesai. Keju siap untuk dijual.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Calculate production statistics.
     */
    private function calculateStats($farmId)
    {
        $currentMonth = Carbon::now()->startOfMonth();

        return [
            'total_productions_month' => CheeseProduction::where('farm_id', $farmId)
                ->where('production_date', '>=', $currentMonth)
                ->count(),
            'total_cheese_weight_month' => CheeseProduction::where('farm_id', $farmId)
                ->where('production_date', '>=', $currentMonth)
                ->sum('cheese_weight_kg'),
            'in_production' => CheeseProduction::where('farm_id', $farmId)
                ->where('status', 'in_production')
                ->count(),
            'aging' => CheeseProduction::where('farm_id', $farmId)
                ->where('status', 'aging')
                ->count(),
            'completed' => CheeseProduction::where('farm_id', $farmId)
                ->where('status', 'completed')
                ->count(),
            'avg_yield' => CheeseProduction::where('farm_id', $farmId)
                ->whereNotNull('yield_percentage')
                ->average('yield_percentage'),
        ];
    }
}
