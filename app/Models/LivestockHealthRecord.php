<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class LivestockHealthRecord extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'livestock_id',
        'health_status',
        'diagnosis',
        'treatment',
        'notes',
        'medicines',
        'record_date',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'diagnosis' => 'array', // Cast JSON to array
            'treatment' => 'array', // Cast JSON to array
            'medicines' => 'array', // Cast JSON to array
        ];
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }
}
