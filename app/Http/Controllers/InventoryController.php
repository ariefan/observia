<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
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

        // Get items expiring soon (within 30 days)
        $expiringCount = InventoryBatch::whereHas('inventoryItem', function ($query) use ($currentFarmId) {
                $query->where('farm_id', $currentFarmId);
            })
            ->where('is_active', true)
            ->where('current_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->count();

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

        // Get expiring items count (within 30 days)
        $expiringItems = InventoryBatch::whereHas('inventoryItem', function ($query) use ($currentFarmId) {
                $query->where('farm_id', $currentFarmId);
            })
            ->where('is_active', true)
            ->where('current_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->count();

        // Get recent transactions (last 10)
        $recentTransactions = InventoryTransaction::with(['inventoryItem', 'user'])
            ->whereHas('inventoryItem', function ($query) use ($currentFarmId) {
                $query->where('farm_id', $currentFarmId);
            })
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'item_name' => $transaction->inventoryItem->name ?? 'Unknown',
                    'type' => $transaction->type,
                    'quantity' => $transaction->quantity,
                    'user_name' => $transaction->user->name ?? 'System',
                    'transaction_date' => $transaction->transaction_date,
                    'notes' => $transaction->notes,
                ];
            });

        return Inertia::render('Inventory/Dashboard', [
            'stats' => [
                'totalItems' => $totalItems,
                'lowStockItems' => $lowStockItems,
                'expiringItems' => $expiringItems,
                'totalValue' => $totalValue,
            ],
            'categories' => $categoryStats,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
