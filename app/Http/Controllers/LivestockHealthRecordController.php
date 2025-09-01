<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LivestockHealthRecord;
use App\Models\Livestock;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Enum\StatusLivestock;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\HasCurrentFarm;
use Illuminate\Support\Facades\DB;

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
                'livestockStats' => [
                    'total' => 0,
                    'healthy' => 0,
                    'sick' => 0
                ]
            ]);
        }

        $healthRecords = LivestockHealthRecord::with(['livestock.breed'])
            ->whereHas('livestock', function ($query) use ($currentFarmId) {
                $query->where('farm_id', $currentFarmId)
                      ->where('status', StatusLivestock::ACTIVE);
            })
            ->orderBy('record_date', 'desc')
            ->paginate(15);

        // Get total active livestock count in the farm
        $totalLivestock = Livestock::where('farm_id', $currentFarmId)
            ->where('status', StatusLivestock::ACTIVE)
            ->count();

        // Get count of active livestock with 'sick' status in their latest health record
        $sickLivestockCount = DB::table('livestocks')
            ->where('farm_id', $currentFarmId)
            ->where('status', StatusLivestock::ACTIVE->value)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('livestock_health_records as lhr1')
                    ->whereColumn('lhr1.livestock_id', 'livestocks.id')
                    ->where('lhr1.health_status', 'sick')
                    ->whereNotExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('livestock_health_records as lhr2')
                            ->whereColumn('lhr2.livestock_id', 'lhr1.livestock_id')
                            ->whereColumn('lhr2.record_date', '>', 'lhr1.record_date');
                    });
            })
            ->count();

        // Healthy livestock = Total livestock - Sick livestock
        $healthyLivestockCount = $totalLivestock - $sickLivestockCount;

        return Inertia::render('HealthRecords/Index', [
            'healthRecords' => $healthRecords,
            'livestockStats' => [
                'total' => $totalLivestock,
                'healthy' => $healthyLivestockCount,
                'sick' => $sickLivestockCount
            ]
        ]);
    }

    public function create()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return redirect()->back()->with('error', 'No farm selected.');
        }

        $livestocks = Livestock::where('farm_id', $currentFarmId)
            ->where('status', StatusLivestock::ACTIVE)
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

        // Get available inventory medicines for this farm
        $inventoryMedicines = InventoryItem::with(['unit', 'category'])
            ->where('farm_id', $currentFarmId)
            ->whereHas('category', function($query) {
                $query->where('name', 'Obat-obatan');
            })
            ->where('current_stock', '>', 0)
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'stock' => $item->current_stock,
                    'unit' => [
                        'id' => $item->unit->id,
                        'name' => $item->unit->name,
                        'symbol' => $item->unit->symbol,
                    ],
                    'category' => [
                        'id' => $item->category->id,
                        'name' => $item->category->name,
                    ]
                ];
            });


        return Inertia::render('HealthRecords/Create', [
            'livestocks' => $livestocks,
            'inventoryMedicines' => $inventoryMedicines,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'livestock_id' => 'required|exists:livestocks,id',
            'health_status' => 'required|in:healthy,sick',
            'diagnosis' => 'nullable|array',
            'diagnosis.*' => 'nullable|string|max:255',
            'treatment' => 'nullable|array',
            'treatment.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.inventory_item_id' => 'nullable|integer|exists:inventory_items,id',
            'medicines.*.name' => 'nullable|string|max:255',
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

        DB::transaction(function () use ($validated, $request) {
            // Create the health record
            $healthRecord = LivestockHealthRecord::create($validated);
            
            // Process medicine inventory transactions
            if (isset($validated['medicines'])) {
                foreach ($validated['medicines'] as $medicine) {
                    if (!empty($medicine['inventory_item_id']) && !empty($medicine['quantity'])) {
                        $inventoryItem = InventoryItem::findOrFail($medicine['inventory_item_id']);
                        
                        // Check if we have enough stock
                        if ($inventoryItem->current_stock < $medicine['quantity']) {
                            throw new \Exception("Insufficient stock for {$medicine['name']}. Available: {$inventoryItem->current_stock}, Required: {$medicine['quantity']}");
                        }
                        
                        // Create inventory transaction
                        InventoryTransaction::create([
                            'inventory_item_id' => $medicine['inventory_item_id'],
                            'user_id' => $request->user()->id,
                            'type' => 'out',
                            'reference_type' => 'health_record',
                            'reference_id' => $healthRecord->id,
                            'quantity' => -$medicine['quantity'], // Negative for outgoing
                            'notes' => "Used for {$healthRecord->livestock->name} ({$healthRecord->livestock->tag_id}) - " . ($medicine['dosage'] ?? 'No dosage specified'),
                            'metadata' => json_encode([
                                'dosage' => $medicine['dosage'] ?? null,
                                'livestock_id' => $healthRecord->livestock_id,
                                'health_record_id' => $healthRecord->id
                            ]),
                            'transaction_date' => now(),
                        ]);
                        
                        // Update inventory stock
                        $inventoryItem->decrement('current_stock', $medicine['quantity']);
                    }
                }
            }
        });

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
            ->where('status', StatusLivestock::ACTIVE)
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

        // Get available inventory medicines for this farm
        $inventoryMedicines = InventoryItem::with(['unit', 'category'])
            ->where('farm_id', $currentFarmId)
            ->whereHas('category', function($query) {
                $query->where('name', 'Obat-obatan');
            })
            ->where('current_stock', '>', 0)
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'stock' => $item->current_stock,
                    'unit' => [
                        'id' => $item->unit->id,
                        'name' => $item->unit->name,
                        'symbol' => $item->unit->symbol,
                    ],
                    'category' => [
                        'id' => $item->category->id,
                        'name' => $item->category->name,
                    ]
                ];
            });

        return Inertia::render('HealthRecords/Edit', [
            'healthRecord' => $healthRecord,
            'livestocks' => $livestocks,
            'inventoryMedicines' => $inventoryMedicines,
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
            'diagnosis.*' => 'nullable|string|max:255',
            'treatment' => 'nullable|array',
            'treatment.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.inventory_item_id' => 'nullable|integer|exists:inventory_items,id',
            'medicines.*.name' => 'nullable|string|max:255',
            'medicines.*.type' => 'nullable|string|max:255',
            'medicines.*.quantity' => 'nullable|integer|min:1',
            'medicines.*.dosage' => 'nullable|string|max:255',
            'record_date' => 'required|date',
        ]);

        $livestock = Livestock::findOrFail($validated['livestock_id']);
        if ($livestock->farm_id !== $currentFarmId) {
            return redirect()->back()->withErrors(['livestock_id' => 'Invalid livestock selection.']);
        }

        DB::transaction(function () use ($healthRecord, $validated, $request) {
            // Reverse previous inventory transactions for this health record
            $previousTransactions = InventoryTransaction::where('reference_type', 'health_record')
                ->where('reference_id', $healthRecord->id)
                ->get();
                
            foreach ($previousTransactions as $transaction) {
                // Restore inventory stock (reverse the negative quantity)
                $transaction->inventoryItem->increment('current_stock', abs($transaction->quantity));
                // Delete the transaction
                $transaction->delete();
            }
            
            // Update the health record
            $healthRecord->update($validated);
            
            // Process new medicine inventory transactions
            if (isset($validated['medicines'])) {
                foreach ($validated['medicines'] as $medicine) {
                    if (!empty($medicine['inventory_item_id']) && !empty($medicine['quantity'])) {
                        $inventoryItem = InventoryItem::findOrFail($medicine['inventory_item_id']);
                        
                        // Check if we have enough stock
                        if ($inventoryItem->current_stock < $medicine['quantity']) {
                            throw new \Exception("Insufficient stock for {$medicine['name']}. Available: {$inventoryItem->current_stock}, Required: {$medicine['quantity']}");
                        }
                        
                        // Create inventory transaction
                        InventoryTransaction::create([
                            'inventory_item_id' => $medicine['inventory_item_id'],
                            'user_id' => $request->user()->id,
                            'type' => 'out',
                            'reference_type' => 'health_record',
                            'reference_id' => $healthRecord->id,
                            'quantity' => -$medicine['quantity'], // Negative for outgoing
                            'notes' => "Used for {$healthRecord->livestock->name} ({$healthRecord->livestock->tag_id}) - " . ($medicine['dosage'] ?? 'No dosage specified'),
                            'metadata' => json_encode([
                                'dosage' => $medicine['dosage'] ?? null,
                                'livestock_id' => $healthRecord->livestock_id,
                                'health_record_id' => $healthRecord->id
                            ]),
                            'transaction_date' => now(),
                        ]);
                        
                        // Update inventory stock
                        $inventoryItem->decrement('current_stock', $medicine['quantity']);
                    }
                }
            }
        });

        return redirect()->route('health-records.index')->with('success', 'Catatan kesehatan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $healthRecord = LivestockHealthRecord::findOrFail($id);
        
        $currentFarmId = $this->getCurrentFarmId();
        if ($healthRecord->livestock->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Reverse inventory transactions for this health record before deleting
        $transactions = InventoryTransaction::where('reference_type', 'health_record')
            ->where('reference_id', $healthRecord->id)
            ->get();
            
        foreach ($transactions as $transaction) {
            // Restore inventory stock (reverse the negative quantity)
            $transaction->inventoryItem->increment('current_stock', abs($transaction->quantity));
            // Delete the transaction
            $transaction->delete();
        }
        
        $healthRecord->delete();

        return redirect()->route('health-records.index')->with('success', 'Catatan kesehatan berhasil dihapus.');
    }
}
