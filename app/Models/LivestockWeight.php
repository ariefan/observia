<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivestockWeight extends Model
{
    use HasFactory;

    protected $fillable = ['livestock_id', 'weight', 'date', 'user_id'];
    
    protected static function booted()
    {
        static::created(function ($weight) {
            $weight->livestock()->update(['weight' => $weight->weight]);
        });
    }

    public function livestock()
    {
        return $this->belongsTo(Livestock::class);
    }
}
