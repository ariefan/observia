<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockMilking extends Model
{
    use HasFactory;

    protected $table = 'livestock_milking';

    protected $fillable = [
        'livestock_id', 
        'milk_volume', 
        'date', 
        'time', 
        'session', 
        'user_id',
        'device_id',
        'notes'
    ];

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
