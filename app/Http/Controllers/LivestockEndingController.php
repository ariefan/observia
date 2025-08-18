<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LivestockEnding;
use App\Models\Livestock;
use App\Traits\HasCurrentFarm;
use Inertia\Inertia;

class LivestockEndingController extends Controller
{
    use HasCurrentFarm;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $farmId = $this->getCurrentFarmId();
        
        $query = LivestockEnding::with(['livestock', 'recordedBy'])
            ->forFarm($farmId)
            ->orderBy('ending_date', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('livestock', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('aifarm_id', 'like', "%{$search}%")
                  ->orWhere('tag_id', 'like', "%{$search}%");
            });
        }

        $endings = $query->paginate(15);

        return Inertia::render('LivestockEndings/Index', [
            'endings' => $endings,
            'statusOptions' => LivestockEnding::getStatusOptions(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $farmId = $this->getCurrentFarmId();
        
        // Get active livestock for this farm
        $livestocks = Livestock::where('farm_id', $farmId)
            ->where('status', 1) // Only active livestock (StatusLivestock::ACTIVE = 1)
            ->with(['breed.species', 'herd'])
            ->get();

        $selectedLivestock = null;
        if ($request->filled('livestock_id')) {
            $selectedLivestock = $livestocks->firstWhere('id', $request->livestock_id);
        }

        return Inertia::render('LivestockEndings/Create', [
            'livestocks' => $livestocks,
            'selectedLivestock' => $selectedLivestock,
            'statusOptions' => LivestockEnding::getStatusOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $farmId = $this->getCurrentFarmId();
        
        $rules = [
            'livestock_id' => 'required|exists:livestocks,id',
            'ending_date' => 'required|date',
            'ending_status' => 'required|in:' . implode(',', array_keys(LivestockEnding::getStatusOptions())),
            'notes' => 'nullable|string',
        ];

        // Add conditional validation based on status
        $status = $request->ending_status;
        
        if (in_array($status, ['sold', 'gifted'])) {
            $rules['buyer_name'] = 'required|string|max:255';
            $rules['buyer_phone'] = 'required|string|max:20';
            $rules['buyer_email'] = 'nullable|email|max:255';
            if ($status === 'sold') {
                $rules['price'] = 'required|numeric|min:0';
            }
        }
        
        if ($status === 'loaned') {
            $rules['receiving_farm_name'] = 'required|string|max:255';
            $rules['receiver_name'] = 'required|string|max:255';
            $rules['receiver_phone'] = 'required|string|max:20';
            $rules['receiver_email'] = 'nullable|email|max:255';
            $rules['loan_date'] = 'required|date';
            $rules['return_date'] = 'nullable|date|after:loan_date';
        }

        $validated = $request->validate($rules);
        
        // Verify livestock belongs to current farm
        $livestock = Livestock::where('id', $validated['livestock_id'])
            ->where('farm_id', $farmId)
            ->firstOrFail();

        // Create the ending record
        $ending = LivestockEnding::create([
            ...$validated,
            'farm_id' => $farmId,
            'recorded_by' => auth()->id(),
        ]);

        // Update livestock status based on ending type
        $statusMap = [
            'sold' => 2, // StatusLivestock::SOLD
            'died' => 3, // StatusLivestock::DEAD
            'slaughtered' => 4, // StatusLivestock::SLAUGHTERED
            'gifted' => 2, // StatusLivestock::SOLD (closest equivalent)
            'loaned' => 1, // Keep active for loaned livestock
        ];
        
        $livestock->update(['status' => $statusMap[$status] ?? 2]);

        return redirect()->route('livestocks.show', $livestock->id)
            ->with('success', 'Pengakhiran ternak berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LivestockEnding $livestockEnding)
    {
        $farmId = $this->getCurrentFarmId();
        
        // Ensure the ending belongs to current farm
        if ($livestockEnding->farm_id !== $farmId) {
            abort(403);
        }

        $livestockEnding->load(['livestock.breed.species', 'recordedBy']);

        return Inertia::render('LivestockEndings/Show', [
            'ending' => $livestockEnding,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LivestockEnding $livestockEnding)
    {
        $farmId = $this->getCurrentFarmId();
        
        // Ensure the ending belongs to current farm
        if ($livestockEnding->farm_id !== $farmId) {
            abort(403);
        }

        $livestockEnding->load(['livestock']);

        return Inertia::render('LivestockEndings/Edit', [
            'ending' => $livestockEnding,
            'statusOptions' => LivestockEnding::getStatusOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LivestockEnding $livestockEnding)
    {
        $farmId = $this->getCurrentFarmId();
        
        // Ensure the ending belongs to current farm
        if ($livestockEnding->farm_id !== $farmId) {
            abort(403);
        }

        $rules = [
            'ending_date' => 'required|date',
            'ending_status' => 'required|in:' . implode(',', array_keys(LivestockEnding::getStatusOptions())),
            'notes' => 'nullable|string',
        ];

        // Add conditional validation based on status
        $status = $request->ending_status;
        
        if (in_array($status, ['sold', 'gifted'])) {
            $rules['buyer_name'] = 'required|string|max:255';
            $rules['buyer_phone'] = 'required|string|max:20';
            $rules['buyer_email'] = 'nullable|email|max:255';
            if ($status === 'sold') {
                $rules['price'] = 'required|numeric|min:0';
            }
        }
        
        if ($status === 'loaned') {
            $rules['receiving_farm_name'] = 'required|string|max:255';
            $rules['receiver_name'] = 'required|string|max:255';
            $rules['receiver_phone'] = 'required|string|max:20';
            $rules['receiver_email'] = 'nullable|email|max:255';
            $rules['loan_date'] = 'required|date';
            $rules['return_date'] = 'nullable|date|after:loan_date';
        }

        $validated = $request->validate($rules);
        
        $livestockEnding->update($validated);

        return redirect()->route('livestock-endings.index')
            ->with('success', 'Data pengakhiran ternak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LivestockEnding $livestockEnding)
    {
        $farmId = $this->getCurrentFarmId();
        
        // Ensure the ending belongs to current farm
        if ($livestockEnding->farm_id !== $farmId) {
            abort(403);
        }

        // Reactivate the livestock
        $livestockEnding->livestock->update(['status' => null]);
        
        $livestockEnding->delete();

        return redirect()->route('livestock-endings.index')
            ->with('success', 'Data pengakhiran ternak berhasil dihapus dan ternak diaktifkan kembali.');
    }
}
