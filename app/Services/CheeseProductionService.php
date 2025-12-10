<?php

namespace App\Services;

use App\Models\CheeseProduction;
use App\Models\MilkBatch;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryTransaction;
use App\Models\InventoryBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheeseProductionService
{
    /**
     * Create a new cheese production batch.
     */
    public function createProduction(string $farmId, array $data): CheeseProduction
    {
        return DB::transaction(function () use ($farmId, $data) {
            // Verify milk batches are available and approved
            $milkBatches = MilkBatch::whereIn('id', $data['milk_batch_ids'])
                ->where('farm_id', $farmId)
                ->where('status', 'approved')
                ->get();

            if ($milkBatches->count() !== count($data['milk_batch_ids'])) {
                throw new \Exception('Beberapa batch susu tidak tersedia atau belum disetujui.');
            }

            // Calculate total milk volume
            $totalMilkVolume = $milkBatches->sum('total_volume');

            // Generate batch code
            $batchCode = $this->generateBatchCode($data['production_date']);

            // Calculate yield percentage if weight is provided
            $yieldPercentage = null;
            if (isset($data['cheese_weight_kg']) && $totalMilkVolume > 0) {
                $yieldPercentage = round(($data['cheese_weight_kg'] / $totalMilkVolume) * 100, 2);
            }

            // Determine initial status
            $status = 'in_production';
            $agingStartDate = null;

            if ($data['aging_target_days'] ?? 0 > 0) {
                $status = 'aging';
                $agingStartDate = isset($data['cheese_weight_kg'])
                    ? Carbon::parse($data['production_date'])
                    : null;
            }

            // Create cheese production record
            $production = CheeseProduction::create([
                'batch_code' => $batchCode,
                'farm_id' => $farmId,
                'cheese_type' => $data['cheese_type'],
                'production_date' => $data['production_date'],
                'produced_by_user_id' => Auth::id(),
                'milk_batch_ids' => $data['milk_batch_ids'],
                'total_milk_volume' => $totalMilkVolume,
                'recipe_notes' => $data['recipe_notes'] ?? null,
                'starter_culture' => $data['starter_culture'] ?? null,
                'rennet_type' => $data['rennet_type'] ?? null,
                'rennet_amount' => $data['rennet_amount'] ?? null,
                'additional_ingredients' => $data['additional_ingredients'] ?? null,
                'process_parameters' => $data['process_parameters'] ?? null,
                'cheese_weight_kg' => $data['cheese_weight_kg'] ?? null,
                'yield_percentage' => $yieldPercentage,
                'aging_start_date' => $agingStartDate,
                'aging_target_days' => $data['aging_target_days'] ?? null,
                'status' => $status,
                'storage_location' => $data['storage_location'] ?? null,
            ]);

            // Update milk batches status to mark as used in production
            MilkBatch::whereIn('id', $data['milk_batch_ids'])
                ->update(['status' => 'in_production']);

            // Create inventory item if weight is provided (cheese is produced)
            if (isset($data['cheese_weight_kg']) && $data['cheese_weight_kg'] > 0) {
                $this->createOrUpdateInventoryItem($production, $farmId);
            }

            // TODO: Send notification about new production
            // $this->sendProductionNotification($production);

            return $production;
        });
    }

    /**
     * Complete the aging process.
     */
    public function completeAging(CheeseProduction $production): CheeseProduction
    {
        if ($production->status !== 'aging') {
            throw new \Exception('Production is not in aging status.');
        }

        $production->update([
            'status' => 'completed',
            'aging_completed_at' => now(),
        ]);

        // Update or create inventory item with final status
        if ($production->cheese_weight_kg) {
            $this->createOrUpdateInventoryItem($production, $production->farm_id);
        }

        return $production->fresh();
    }

    /**
     * Create or update inventory item for the cheese production.
     */
    private function createOrUpdateInventoryItem(CheeseProduction $production, string $farmId)
    {
        // Find or create cheese category
        $cheeseCategory = InventoryCategory::firstOrCreate(
            ['name' => 'Produk Susu', 'farm_id' => null],
            [
                'description' => 'Produk keju dan susu olahan',
                'color' => '#FFA500',
                'is_active' => true,
            ]
        );

        // Find or create inventory item for this cheese type
        $inventoryItem = InventoryItem::firstOrCreate(
            [
                'farm_id' => $farmId,
                'category_id' => $cheeseCategory->id,
                'name' => $production->cheese_type,
            ],
            [
                'description' => "Keju {$production->cheese_type} produksi sendiri",
                'unit_id' => 1, // Assuming kg unit exists
                'current_stock' => 0,
                'minimum_stock' => 1,
                'track_expiry' => true,
                'track_batch' => true,
                'is_active' => true,
            ]
        );

        // Create inventory transaction for production
        $transaction = InventoryTransaction::create([
            'farm_id' => $farmId,
            'inventory_item_id' => $inventoryItem->id,
            'transaction_type' => 'production',
            'transaction_date' => $production->production_date,
            'quantity' => $production->cheese_weight_kg,
            'unit_cost' => 0, // Can be calculated based on milk cost
            'total_cost' => 0,
            'reference_type' => 'cheese_production',
            'reference_id' => $production->id,
            'notes' => "Produksi keju batch: {$production->batch_code}",
            'user_id' => Auth::id(),
        ]);

        // Create inventory batch for traceability
        $expiryDate = null;
        if ($production->aging_completed_at) {
            // Cheese typically lasts 30-90 days after aging
            $expiryDate = Carbon::parse($production->aging_completed_at)->addDays(60);
        }

        $inventoryBatch = InventoryBatch::create([
            'inventory_item_id' => $inventoryItem->id,
            'batch_number' => $production->batch_code,
            'quantity' => $production->cheese_weight_kg,
            'remaining_quantity' => $production->cheese_weight_kg,
            'production_date' => $production->production_date,
            'expiry_date' => $expiryDate,
            'supplier' => 'Produksi Sendiri',
            'notes' => "Keju {$production->cheese_type}, aging {$production->aging_target_days} hari",
        ]);

        // Update inventory item current stock
        $inventoryItem->increment('current_stock', $production->cheese_weight_kg);

        // Link production to inventory item
        $production->update(['inventory_item_id' => $inventoryItem->id]);

        return $inventoryItem;
    }

    /**
     * Generate unique batch code for cheese production.
     */
    private function generateBatchCode(string $productionDate): string
    {
        $date = Carbon::parse($productionDate);
        $dateStr = $date->format('Ymd');

        // Get last batch code for this date
        $lastBatch = CheeseProduction::where('batch_code', 'LIKE', "CP-{$dateStr}-%")
            ->orderBy('batch_code', 'desc')
            ->first();

        $sequence = 1;
        if ($lastBatch) {
            // Extract sequence number from last batch code
            $parts = explode('-', $lastBatch->batch_code);
            $sequence = (int)end($parts) + 1;
        }

        return sprintf('CP-%s-%03d', $dateStr, $sequence);
    }

    /**
     * Calculate production summary for a farm.
     */
    public function getProductionSummary(string $farmId, Carbon $startDate, Carbon $endDate): array
    {
        $productions = CheeseProduction::where('farm_id', $farmId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->get();

        $byCheeseType = $productions->groupBy('cheese_type')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total_weight' => $items->sum('cheese_weight_kg'),
                'avg_yield' => $items->whereNotNull('yield_percentage')->avg('yield_percentage'),
            ];
        });

        return [
            'total_productions' => $productions->count(),
            'total_weight' => $productions->sum('cheese_weight_kg'),
            'total_milk_used' => $productions->sum('total_milk_volume'),
            'avg_yield' => $productions->whereNotNull('yield_percentage')->avg('yield_percentage'),
            'by_cheese_type' => $byCheeseType,
            'by_status' => $productions->groupBy('status')->map->count(),
        ];
    }
}
