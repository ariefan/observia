<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkBatch extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'batch_code',
        'farm_id',
        'collection_date',
        'session',
        'total_volume',
        'source_livestock_milking_ids',
        'collected_by_user_id',
        'collected_at',
        'estimated_volume',
        'actual_volume',
        'variance_percentage',
        'transport_temp_pickup',
        'transport_temp_delivery',
        'transport_duration_minutes',
        'transport_notes',
        'received_by_user_id',
        'received_at',
        'visual_check',
        'smell_check',
        'quality_tested_by_user_id',
        'quality_tested_at',
        'quality_data',
        'quality_grade',
        'quality_notes',
        'status',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'collected_at' => 'datetime',
        'received_at' => 'datetime',
        'quality_tested_at' => 'datetime',
        'source_livestock_milking_ids' => 'array',
        'quality_data' => 'array',
        'metadata' => 'array',
    ];

    protected $auditExclude = ['metadata'];

    // Relationships
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function qualityTestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_tested_by_user_id');
    }

    protected function getAuditMetadata(): array
    {
        return [
            'batch_code' => $this->batch_code,
            'farm_name' => $this->farm->name ?? null,
            'status' => $this->status,
        ];
    }
}
