<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function livestocks()
    {
        return $this->hasMany(Livestock::class);
    }

    public function getCurrentCapacityAttribute()
    {
        return $this->livestocks()->count();
    }

    public function feedings()
    {
        return $this->hasMany(HerdFeeding::class);
    }
}
