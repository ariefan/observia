<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheeseProduction extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'batch_code',
        'farm_id',
        'cheese_type',
        'production_date',
        'produced_by_user_id',
        'milk_batch_ids',
        'total_milk_volume',
        'recipe_notes',
        'starter_culture',
        'rennet_type',
        'rennet_amount',
        'additional_ingredients',
        'process_parameters',
        'cheese_weight_kg',
        'yield_percentage',
        'aging_start_date',
        'aging_target_days',
        'aging_completed_at',
        'aging_notes',
        'status',
        'inventory_item_id',
        'storage_location',
        'process_photos',
        'metadata',
    ];

    protected $casts = [
        'production_date' => 'date',
        'aging_start_date' => 'date',
        'aging_completed_at' => 'datetime',
        'milk_batch_ids' => 'array',
        'additional_ingredients' => 'array',
        'process_parameters' => 'array',
        'aging_notes' => 'array',
        'process_photos' => 'array',
        'metadata' => 'array',
    ];

    protected $auditExclude = ['metadata', 'process_photos'];

    // Relationships
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function producedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'produced_by_user_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    protected function getAuditMetadata(): array
    {
        return [
            'batch_code' => $this->batch_code,
            'cheese_type' => $this->cheese_type,
            'farm_name' => $this->farm->name ?? null,
            'status' => $this->status,
        ];
    }
}
