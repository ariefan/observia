<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivestockWeight extends Model
{
    use HasFactory;

    protected $fillable = ['livestock_id', 'weight', 'date'];

    public function livestock()
    {
        return $this->belongsTo(Livestock::class);
    }
}
