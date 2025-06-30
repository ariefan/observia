<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read_at',
        'action_required',
        'action_status',
        'acted_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'acted_at' => 'datetime',
        'action_required' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requiresAction(): bool
    {
        return $this->action_required && $this->action_status === 'pending';
    }

    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function accept()
    {
        $this->update([
            'action_status' => 'accepted',
            'acted_at' => now(),
        ]);
    }

    public function reject()
    {
        $this->update([
            'action_status' => 'rejected',
            'acted_at' => now(),
        ]);
    }
}
