<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryUnit extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'type',
        'base_factor',
        'is_base',
    ];

    protected $casts = [
        'base_factor' => 'decimal:6',
        'is_base' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'unit_id');
    }
}
