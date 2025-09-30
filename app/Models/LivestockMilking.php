<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockMilking extends Model
{
    use HasFactory, Auditable;

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

    protected $auditExclude = ['device_id'];

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function getAuditMetadata(): array
    {
        return [
            'livestock_name' => $this->livestock->name ?? null,
            'livestock_id' => $this->livestock_id,
            'user_name' => $this->user->name ?? auth()->user()->name ?? null,
        ];
    }
}
