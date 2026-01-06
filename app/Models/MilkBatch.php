<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MilkBatch extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'batch_code',
        'tracking_number',
        'farm_id',
        'destination_farm_id',
        'collection_date',
        'session',
        'total_volume',
        'source_livestock_milking_ids',
        'collected_by_user_id',
        'courier_user_id',
        'courier_name',
        'courier_phone',
        'vehicle_number',
        'collected_at',
        'dispatched_at',
        'delivered_at',
        'expected_delivery_at',
        'estimated_volume',
        'actual_volume',
        'variance_percentage',
        'transport_temp_pickup',
        'transport_temp_delivery',
        'transport_duration_minutes',
        'transport_notes',
        'transport_photos',
        'delivery_notes',
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
        'transport_status',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'collected_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'expected_delivery_at' => 'datetime',
        'received_at' => 'datetime',
        'quality_tested_at' => 'datetime',
        'source_livestock_milking_ids' => 'array',
        'quality_data' => 'array',
        'transport_photos' => 'array',
        'metadata' => 'array',
    ];

    protected $auditExclude = ['metadata'];

    // Relationships
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function destinationFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'destination_farm_id');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function qualityTestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_tested_by_user_id');
    }

    /**
     * Get cheese productions that use this milk batch.
     * This is a custom relationship since CheeseProduction stores batch IDs in a JSON array.
     */
    public function cheeseProductions(): Builder
    {
        return CheeseProduction::query()
            ->whereRaw("milk_batch_ids::jsonb @> ?::jsonb", [json_encode([$this->id])]);
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
