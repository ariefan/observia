<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name', 'code', 'binomial_nomenclature',
    ];

    public function breeds(): HasMany
    {
        return $this->hasMany(Breed::class);
    }
}
