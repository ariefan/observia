<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'farm_id',
        'category_id',
        'unit_id',
        'name',
        'brand',
        'description',
        'sku',
        'unit_cost',
        'selling_price',
        'minimum_stock',
        'current_stock',
        'track_expiry',
        'track_batch',
        'is_active',
        'specifications',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'minimum_stock' => 'decimal:3',
        'current_stock' => 'decimal:3',
        'track_expiry' => 'boolean',
        'track_batch' => 'boolean',
        'is_active' => 'boolean',
        'specifications' => 'array',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
