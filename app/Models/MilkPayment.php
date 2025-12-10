<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkPayment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'farm_id',
        'payment_period_start',
        'payment_period_end',
        'total_liters',
        'grade_breakdown',
        'gross_amount',
        'deductions',
        'deductions_total',
        'net_amount',
        'status',
        'calculated_by_user_id',
        'calculated_at',
        'approved_by_user_id',
        'approved_at',
        'paid_by_user_id',
        'paid_at',
        'payment_method',
        'payment_reference',
        'payment_proof_path',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'payment_period_start' => 'date',
        'payment_period_end' => 'date',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'grade_breakdown' => 'array',
        'deductions' => 'array',
        'metadata' => 'array',
    ];

    protected $auditExclude = ['metadata'];

    // Relationships
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    protected function getAuditMetadata(): array
    {
        return [
            'farm_name' => $this->farm->name ?? null,
            'period' => $this->payment_period_start->format('Y-m-d') . ' to ' . $this->payment_period_end->format('Y-m-d'),
            'status' => $this->status,
            'net_amount' => $this->net_amount,
        ];
    }
}
