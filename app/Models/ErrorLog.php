<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'level',
        'message',
        'context',
        'file',
        'line',
        'stack_trace',
        'url',
        'ip_address',
        'user_agent',
        'user_id',
        'farm_id',
    ];

    protected $casts = [
        'context' => 'array',
        'line' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function scopeForLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getLevelNameAttribute(): string
    {
        return match ($this->level) {
            'emergency' => 'Darurat',
            'alert' => 'Peringatan',
            'critical' => 'Kritis',
            'error' => 'Error',
            'warning' => 'Peringatan',
            'notice' => 'Pemberitahuan',
            'info' => 'Informasi',
            'debug' => 'Debug',
            default => ucfirst($this->level),
        };
    }
}
