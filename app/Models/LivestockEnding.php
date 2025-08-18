<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class LivestockEnding extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'livestock_id',
        'farm_id',
        'ending_date',
        'ending_status',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'price',
        'receiving_farm_name',
        'receiver_name',
        'receiver_phone',
        'receiver_email',
        'loan_date',
        'return_date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'ending_date' => 'date',
        'loan_date' => 'date',
        'return_date' => 'date',
        'price' => 'decimal:2',
    ];

    // Status constants
    const STATUS_SOLD = 'sold';
    const STATUS_GIFTED = 'gifted';
    const STATUS_LOANED = 'loaned';
    const STATUS_DIED = 'died';
    const STATUS_SLAUGHTERED = 'slaughtered';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_SOLD => 'Dijual',
            self::STATUS_GIFTED => 'Hibah',
            self::STATUS_LOANED => 'Dipinjam',
            self::STATUS_DIED => 'Mati',
            self::STATUS_SLAUGHTERED => 'Dipotong',
        ];
    }

    public function getStatusLabelAttribute()
    {
        return self::getStatusOptions()[$this->ending_status] ?? $this->ending_status;
    }

    // Relationships
    public function livestock()
    {
        return $this->belongsTo(Livestock::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Scopes
    public function scopeForFarm($query, $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('ending_status', $status);
    }
}
