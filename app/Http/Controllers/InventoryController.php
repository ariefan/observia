<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Traits\HasCurrentFarm;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use HasCurrentFarm;

    public function index()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('Inventory/Index', [
                'inventoryItems' => ['data' => [], 'total' => 0],
                'categories' => [],
                'lowStockCount' => 0,
                'expiringCount' => 0,
            ]);
        }

        $inventoryItems = InventoryItem::with(['category', 'unit'])
            ->where('farm_id', $currentFarmId)
            ->where('is_active', true)
            ->paginate(15);

        $categories = InventoryCategory::where('is_active', true)->get();

        // Get low stock items count
        $lowStockCount = InventoryItem::where('farm_id', $currentFarmId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('is_active', true)
            ->count();

        // Get items expiring soon (within 30 days) - placeholder for now
        $expiringCount = 0; // TODO: Calculate from batches

        return Inertia::render('Inventory/Index', [
            'inventoryItems' => $inventoryItems,
            'categories' => $categories,
            'lowStockCount' => $lowStockCount,
            'expiringCount' => $expiringCount,
        ]);
    }

    public function dashboard()
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('Inventory/Dashboard', [
                'stats' => [
                    'totalItems' => 0,
                    'lowStockItems' => 0,
                    'expiringItems' => 0,
                    'totalValue' => 0,
                ],
                'categories' => [],
                'recentTransactions' => [],
            ]);
        }

        $totalItems = InventoryItem::where('farm_id', $currentFarmId)
            ->where('is_active', true)
            ->count();

        $lowStockItems = InventoryItem::where('farm_id', $currentFarmId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('is_active', true)
            ->count();

        $totalValue = InventoryItem::where('farm_id', $currentFarmId)
            ->where('is_active', true)
            ->sum(\DB::raw('current_stock * unit_cost'));

        $categoryStats = InventoryCategory::withCount(['items' => function ($query) use ($currentFarmId) {
            $query->where('farm_id', $currentFarmId)->where('is_active', true);
        }])->get();

        return Inertia::render('Inventory/Dashboard', [
            'stats' => [
                'totalItems' => $totalItems,
                'lowStockItems' => $lowStockItems,
                'expiringItems' => 0, // TODO: Calculate from batches
                'totalValue' => $totalValue,
            ],
            'categories' => $categoryStats,
            'recentTransactions' => [], // TODO: Get recent transactions
        ]);
    }
}
