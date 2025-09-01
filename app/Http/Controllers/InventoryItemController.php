<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Traits\HasCurrentFarm;
use Inertia\Inertia;

class InventoryItemController extends Controller
{
    use HasCurrentFarm;

    public function index()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('Inventory/Items/Index', [
                'items' => ['data' => [], 'total' => 0],
                'categories' => [],
            ]);
        }

        $items = InventoryItem::with(['category', 'unit'])
            ->where('farm_id', $currentFarmId)
            ->where('is_active', true)
            ->paginate(15);

        $categories = InventoryCategory::where('is_active', true)->get();

        return Inertia::render('Inventory/Items/Index', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $categories = InventoryCategory::where('is_active', true)->get();
        $units = InventoryUnit::get();

        return Inertia::render('Inventory/Items/Create', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return redirect()->back()->withErrors(['farm' => 'No farm selected.']);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'unit_id' => 'required|exists:inventory_units,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'track_expiry' => 'boolean',
            'track_batch' => 'boolean',
            'specifications' => 'nullable|array',
        ]);

        $validated['farm_id'] = $currentFarmId;

        InventoryItem::create($validated);

        return redirect()->route('inventory.items.index')
            ->with('success', 'Item inventaris berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        $item = InventoryItem::with(['category', 'unit', 'transactions' => function ($query) {
            $query->with('user:id,name')->orderBy('transaction_date', 'desc')->limit(10);
        }])->findOrFail($id);
        
        // Verify the item belongs to the current farm
        if ($item->farm_id !== $currentFarmId) {
            abort(403);
        }

        return Inertia::render('Inventory/Items/Show', [
            'item' => $item,
        ]);
    }

    public function edit(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        $item = InventoryItem::with(['category', 'unit'])->findOrFail($id);
        
        // Verify the item belongs to the current farm
        if ($item->farm_id !== $currentFarmId) {
            abort(403);
        }

        $categories = InventoryCategory::where('is_active', true)->get();
        $units = InventoryUnit::get();

        return Inertia::render('Inventory/Items/Edit', [
            'item' => $item,
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        $item = InventoryItem::findOrFail($id);
        
        // Verify the item belongs to the current farm
        if ($item->farm_id !== $currentFarmId) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'unit_id' => 'required|exists:inventory_units,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'track_expiry' => 'boolean',
            'track_batch' => 'boolean',
            'specifications' => 'nullable|array',
        ]);

        $item->update($validated);

        return redirect()->route('inventory.items.index')
            ->with('success', 'Item inventaris berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        $item = InventoryItem::findOrFail($id);
        
        // Verify the item belongs to the current farm
        if ($item->farm_id !== $currentFarmId) {
            abort(403);
        }

        // Check if item has transactions
        if ($item->transactions()->count() > 0) {
            return redirect()->back()->withErrors(['delete' => 'Cannot delete item with existing transactions.']);
        }

        $item->delete();

        return redirect()->route('inventory.items.index')
            ->with('success', 'Item inventaris berhasil dihapus.');
    }
}
