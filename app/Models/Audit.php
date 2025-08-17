<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'farm_id',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'event_name',
        'model_name',
        'formatted_changes',
    ];

    /**
     * Get the auditable model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the farm context
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Scope to filter by model type
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    /**
     * Scope to filter by event type
     */
    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by farm
     */
    public function scopeForFarm($query, string $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get formatted changes for display
     */
    public function getFormattedChangesAttribute(): array
    {
        $changes = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }
        
        return $changes;
    }

    /**
     * Get human-readable event name
     */
    public function getEventNameAttribute(): string
    {
        return match($this->event) {
            'created' => 'Dibuat',
            'updated' => 'Diubah',
            'deleted' => 'Dihapus',
            'restored' => 'Dipulihkan',
            default => ucfirst($this->event),
        };
    }

    /**
     * Get human-readable model name
     */
    public function getModelNameAttribute(): string
    {
        $modelClass = $this->auditable_type;
        $baseName = class_basename($modelClass);
        
        return match($baseName) {
            'User' => 'Pengguna',
            'Livestock' => 'Ternak',
            'Herd' => 'Kandang',
            'Feed' => 'Pakan',
            'Ration' => 'Ransum',
            'RationItem' => 'Item Ransum',
            'HerdFeeding' => 'Pemberian Pakan',
            'LivestockWeight' => 'Berat Ternak',
            'LivestockMilking' => 'Pemerahan',
            'FeedingLeftover' => 'Sisa Pakan',
            'Farm' => 'Peternakan',
            'HistoryRation' => 'Riwayat Ransum',
            'HistoryRationItem' => 'Riwayat Item Ransum',
            default => $baseName,
        };
    }
}
