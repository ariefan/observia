<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RationItem extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    public function ration(): BelongsTo
    {
        return $this->belongsTo(Ration::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
    public function historyRationItems()
    {
        return $this->hasMany(HistoryRationItem::class, 'ration_item_id');
    }
}
