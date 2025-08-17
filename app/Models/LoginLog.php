<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'user_name',
        'event',
        'ip_address',
        'user_agent',
        'farm_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = [
        'event_name',
    ];

    /**
     * Get the user who performed the login
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
     * Get human-readable event name
     */
    public function getEventNameAttribute(): string
    {
        return match($this->event) {
            'login' => 'Masuk',
            'logout' => 'Keluar',
            'failed_login' => 'Gagal Masuk',
            default => ucfirst($this->event),
        };
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
     * Log a login event
     */
    public static function logLogin(User $user, array $metadata = []): void
    {
        $request = app('request');
        
        self::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_name' => $user->name,
            'event' => 'login',
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'farm_id' => $user->current_farm_id,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log a logout event
     */
    public static function logLogout(User $user, array $metadata = []): void
    {
        $request = app('request');
        
        self::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_name' => $user->name,
            'event' => 'logout',
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'farm_id' => $user->current_farm_id,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log a failed login event
     */
    public static function logFailedLogin(string $email, array $metadata = []): void
    {
        $request = app('request');
        
        self::create([
            'user_id' => null,
            'email' => $email,
            'user_name' => null,
            'event' => 'failed_login',
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'farm_id' => null,
            'metadata' => $metadata,
        ]);
    }
}
