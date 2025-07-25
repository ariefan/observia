<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoryRation extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'id',
        'action',
        'ration_id',
        'farm_id',
        'name',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function historyRationItems(): HasMany
    {
        return $this->hasMany(HistoryRationItem::class);
    }

    public function ration()
    {
        return $this->belongsTo(Ration::class, 'ration_id');
    }
}
