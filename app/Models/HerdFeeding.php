<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HerdFeeding extends Model
{
    use HasFactory;

    protected $fillable = [
        'herd_id',
        'ration_id',
        'quantity',
        'date',
        'time',
        'session',
        'device_id',
        'user_id',
        'notes',
    ];

    public function herd(): BelongsTo
    {
        return $this->belongsTo(Herd::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
