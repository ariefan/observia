<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LivestockHealthRecord;
use App\Models\Livestock;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\HasCurrentFarm;

class LivestockHealthRecordController extends Controller
{
    use AuthorizesRequests;
    use HasCurrentFarm;

    public function index()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('HealthRecords/Index', [
                'healthRecords' => ['data' => [], 'total' => 0],
            ]);
        }

        $healthRecords = LivestockHealthRecord::with(['livestock.breed'])
            ->whereHas('livestock', function ($query) use ($currentFarmId) {
                $query->where('farm_id', $currentFarmId);
            })
            ->orderBy('record_date', 'desc')
            ->paginate(15);

        return Inertia::render('HealthRecords/Index', [
            'healthRecords' => $healthRecords,
        ]);
    }

    public function create()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return redirect()->back()->with('error', 'No farm selected.');
        }

        $livestocks = Livestock::where('farm_id', $currentFarmId)
            ->with('breed')
            ->get()
            ->map(function ($livestock) {
                return [
                    'id' => $livestock->id,
                    'name' => $livestock->name,
                    'tag_id' => $livestock->tag_id,
                    'breed_name' => $livestock->breed->name ?? '',
                ];
            });

        return Inertia::render('HealthRecords/Create', [
            'livestocks' => $livestocks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'livestock_id' => 'required|exists:livestocks,id',
            'health_status' => 'required|in:healthy,sick',
            'diagnosis' => 'nullable|array',
            'diagnosis.*' => 'string|max:255',
            'treatment' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'string|max:255',
            'medicines.*.type' => 'nullable|string|max:255',
            'medicines.*.quantity' => 'nullable|integer|min:1',
            'medicines.*.dosage' => 'nullable|string|max:255',
            'record_date' => 'required|date',
        ]);

        $livestock = Livestock::findOrFail($validated['livestock_id']);
        $currentFarmId = $this->getCurrentFarmId();
        
        if ($livestock->farm_id !== $currentFarmId) {
            return redirect()->back()->withErrors(['livestock_id' => 'Invalid livestock selection.']);
        }

        LivestockHealthRecord::create($validated);

        return redirect()->route('health-records.index')->with('success', 'Catatan kesehatan berhasil disimpan.');
    }

    public function show(string $id)
    {
        $healthRecord = LivestockHealthRecord::with(['livestock.breed'])->findOrFail($id);
        
        $currentFarmId = $this->getCurrentFarmId();
        if ($healthRecord->livestock->farm_id !== $currentFarmId) {
            abort(403);
        }

        return Inertia::render('HealthRecords/Show', [
            'healthRecord' => $healthRecord,
        ]);
    }

    public function edit(string $id)
    {
        $healthRecord = LivestockHealthRecord::with(['livestock.breed'])->findOrFail($id);
        
        $currentFarmId = $this->getCurrentFarmId();
        if ($healthRecord->livestock->farm_id !== $currentFarmId) {
            abort(403);
        }

        $livestocks = Livestock::where('farm_id', $currentFarmId)
            ->with('breed')
            ->get()
            ->map(function ($livestock) {
                return [
                    'id' => $livestock->id,
                    'name' => $livestock->name,
                    'tag_id' => $livestock->tag_id,
                    'breed_name' => $livestock->breed->name ?? '',
                ];
            });

        return Inertia::render('HealthRecords/Edit', [
            'healthRecord' => $healthRecord,
            'livestocks' => $livestocks,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $healthRecord = LivestockHealthRecord::findOrFail($id);
        
        $currentFarmId = $this->getCurrentFarmId();
        if ($healthRecord->livestock->farm_id !== $currentFarmId) {
            abort(403);
        }

        $validated = $request->validate([
            'livestock_id' => 'required|exists:livestocks,id',
            'health_status' => 'required|in:healthy,sick',
            'diagnosis' => 'nullable|array',
            'diagnosis.*' => 'string|max:255',
            'treatment' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'string|max:255',
            'medicines.*.type' => 'nullable|string|max:255',
            'medicines.*.quantity' => 'nullable|integer|min:1',
            'medicines.*.dosage' => 'nullable|string|max:255',
            'record_date' => 'required|date',
        ]);

        $livestock = Livestock::findOrFail($validated['livestock_id']);
        if ($livestock->farm_id !== $currentFarmId) {
            return redirect()->back()->withErrors(['livestock_id' => 'Invalid livestock selection.']);
        }

        $healthRecord->update($validated);

        return redirect()->route('health-records.index')->with('success', 'Catatan kesehatan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $healthRecord = LivestockHealthRecord::findOrFail($id);
        
        $currentFarmId = $this->getCurrentFarmId();
        if ($healthRecord->livestock->farm_id !== $currentFarmId) {
            abort(403);
        }

        $healthRecord->delete();

        return redirect()->route('health-records.index')->with('success', 'Catatan kesehatan berhasil dihapus.');
    }
}
