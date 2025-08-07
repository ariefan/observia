<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingLeftover extends Model
{
    use HasFactory;

    protected $fillable = [
        'feeding_id',
        'leftover_quantity',
        'date',
        'time',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function feeding(): BelongsTo
    {
        return $this->belongsTo(HerdFeeding::class, 'feeding_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
