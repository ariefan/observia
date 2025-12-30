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

    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('Inventory/Items/Index', [
                'items' => ['data' => [], 'total' => 0],
                'categories' => [],
                'filters' => [],
            ]);
        }

        $query = InventoryItem::with(['category', 'unit'])
            ->where('farm_id', $currentFarmId)
            ->where('is_active', true);

        // Search by name or brand
        if ($request->filled('search')) {
            $search = escapeLike($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('brand', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('sku', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Filter by stock status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'empty') {
                $query->whereRaw('current_stock <= minimum_stock');
            } elseif ($status === 'low') {
                $query->whereRaw('current_stock > minimum_stock AND current_stock <= minimum_stock * 1.5');
            } elseif ($status === 'normal') {
                $query->whereRaw('current_stock > minimum_stock * 1.5');
            }
        }

        // Filter by expiry status
        if ($request->filled('expiry_status')) {
            $expiryStatus = $request->get('expiry_status');
            $today = now()->format('Y-m-d');
            
            if ($expiryStatus === 'expired') {
                $query->where('track_expiry', true)
                      ->where('expiry_date', '<', $today);
            } elseif ($expiryStatus === 'expiring_soon') {
                $query->where('track_expiry', true)
                      ->where('expiry_date', '>=', $today)
                      ->where('expiry_date', '<=', now()->addDays(7)->format('Y-m-d'));
            } elseif ($expiryStatus === 'expiring_month') {
                $query->where('track_expiry', true)
                      ->where('expiry_date', '>', now()->addDays(7)->format('Y-m-d'))
                      ->where('expiry_date', '<=', now()->addDays(30)->format('Y-m-d'));
            } elseif ($expiryStatus === 'valid') {
                $query->where('track_expiry', true)
                      ->where('expiry_date', '>', now()->addDays(30)->format('Y-m-d'));
            } elseif ($expiryStatus === 'no_tracking') {
                $query->where('track_expiry', false);
            }
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = InventoryCategory::where('is_active', true)->get();

        return Inertia::render('Inventory/Items/Index', [
            'items' => $items,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
                'status' => $request->get('status'),
                'expiry_status' => $request->get('expiry_status'),
            ],
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
            'expiry_date' => 'nullable|date|after:today',
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
            'expiry_date' => 'nullable|date|after:today',
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
