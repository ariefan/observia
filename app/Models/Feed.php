<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function rationItems(): HasMany
    {
        return $this->hasMany(RationItem::class);
    }
}
