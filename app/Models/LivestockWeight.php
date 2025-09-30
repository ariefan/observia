<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivestockWeight extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['livestock_id', 'weight', 'date', 'user_id'];

    protected static function booted()
    {
        static::created(function ($weight) {
            $latestWeight = self::where('livestock_id', $weight->livestock_id)
            ->orderByDesc('date')
            ->first();

            if ($latestWeight && $weight->date >= $latestWeight->date) {
                $weight->livestock()->update(['weight' => $weight->weight]);
            }
        });
    }

    public function livestock()
    {
        return $this->belongsTo(Livestock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function getAuditMetadata(): array
    {
        return [
            'livestock_name' => $this->livestock->name ?? null,
            'livestock_id' => $this->livestock_id,
            'user_name' => $this->user->name ?? auth()->user()->name ?? null,
        ];
    }
}
