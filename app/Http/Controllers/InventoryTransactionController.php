<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\InventoryItem;
use App\Traits\HasCurrentFarm;
use Inertia\Inertia;

class InventoryTransactionController extends Controller
{
    use HasCurrentFarm;

    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('Inventory/Transactions/Index', [
                'transactions' => ['data' => [], 'total' => 0],
            ]);
        }

        $query = InventoryTransaction::with([
            'inventoryItem.unit', 
            'inventoryItem.category',
            'user:id,name'
        ])
        ->whereHas('inventoryItem', function ($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId);
        });

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by item name
        if ($request->filled('search')) {
            $query->whereHas('inventoryItem', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('brand', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate(15);

        return Inertia::render('Inventory/Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    public function create()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return redirect()->route('inventory.transactions.index')
                ->withErrors(['farm' => 'No farm selected.']);
        }

        $inventoryItems = InventoryItem::where('farm_id', $currentFarmId)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Inventory/Transactions/Create', [
            'inventoryItems' => $inventoryItems,
        ]);
    }

    public function store(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return redirect()->back()->withErrors(['farm' => 'No farm selected.']);
        }

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'type' => 'required|in:in,out,adjustment,expired,damaged',
            'quantity' => 'required|numeric',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date',
            'inventory_batch_id' => 'nullable|exists:inventory_batches,id',
        ]);

        // Verify the inventory item belongs to the current farm
        $inventoryItem = InventoryItem::findOrFail($validated['inventory_item_id']);
        if ($inventoryItem->farm_id !== $currentFarmId) {
            return redirect()->back()->withErrors(['inventory_item_id' => 'Invalid inventory item.']);
        }

        $validated['user_id'] = auth()->id();

        // Create the transaction
        $transaction = InventoryTransaction::create($validated);

        // Update inventory item stock based on transaction type
        if ($validated['type'] === 'in') {
            $inventoryItem->increment('current_stock', $validated['quantity']);
        } elseif (in_array($validated['type'], ['out', 'expired', 'damaged'])) {
            $inventoryItem->decrement('current_stock', $validated['quantity']);
        } elseif ($validated['type'] === 'adjustment') {
            // For adjustments, the quantity can be positive or negative
            $inventoryItem->current_stock = max(0, $inventoryItem->current_stock + $validated['quantity']);
            $inventoryItem->save();
        }

        return redirect()->route('inventory.transactions.index')
            ->with('success', 'Transaksi inventaris berhasil dicatat.');
    }

    public function show(string $id)
    {
        $transaction = InventoryTransaction::with([
            'inventoryItem.unit', 
            'inventoryItem.category',
            'inventoryBatch',
            'user:id,name'
        ])->findOrFail($id);
        
        // Verify the transaction belongs to the current farm
        $currentFarmId = $this->getCurrentFarmId();
        if ($transaction->inventoryItem->farm_id !== $currentFarmId) {
            abort(403);
        }

        return Inertia::render('Inventory/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }
}
