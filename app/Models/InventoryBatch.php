<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBatch extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'original_quantity',
        'current_quantity',
        'reserved_quantity',
        'unit_cost',
        'supplier',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'original_quantity' => 'decimal:3',
        'current_quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the available quantity (current minus reserved).
     */
    public function getAvailableQuantityAttribute(): float
    {
        return $this->current_quantity - $this->reserved_quantity;
    }

    /**
     * Check if batch is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if batch is expiring soon (within 7 days).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isBetween(now(), now()->addDays(7));
    }
}
