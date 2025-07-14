<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Herd extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'farm_id', 'name', 'description', 'status', 'type', 'capacity',
    ];

    protected $appends = ['current_capacity'];

    public function livestocks()
    {
        return $this->hasMany(Livestock::class);
    }

    public function getCurrentCapacityAttribute()
    {
        return $this->livestocks()->count();
    }
}
