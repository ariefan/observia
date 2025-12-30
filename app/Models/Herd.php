<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Herd extends Model
{
    use HasFactory;
    use HasUuids;
    use Auditable;

    protected $fillable = [
        'farm_id', 'name', 'description', 'status', 'type', 'capacity',
    ];

    protected $appends = ['current_capacity'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function livestocks(): HasMany
    {
        return $this->hasMany(Livestock::class);
    }

    public function getCurrentCapacityAttribute(): int
    {
        return $this->livestocks()->count();
    }

    public function feedings(): HasMany
    {
        return $this->hasMany(HerdFeeding::class);
    }
}
