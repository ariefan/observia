<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryRationItem extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'id',
        'history_ration_id',
        'ration_id',
        'feed',
        'quantity',
        'price',
    ];

    public function historyRation()
    {
        return $this->belongsTo(HistoryRation::class);
    }
}
