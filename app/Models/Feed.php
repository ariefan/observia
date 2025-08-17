<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Feed extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $guarded = ['id'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    // public function rationItems(): HasMany
    // {
    //     return $this->hasMany(RationItem::class);
    // }
}
