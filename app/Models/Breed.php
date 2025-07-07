<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Breed extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'species_id', 'code', 'name', 'origin', 'description',
    ];

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
