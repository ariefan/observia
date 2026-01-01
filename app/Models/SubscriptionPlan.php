<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'annual_price',
        'features',
        'max_livestock',
        'max_users',
        'has_analytics',
        'has_iot',
        'has_expert_support',
        'is_active',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'max_livestock' => 'integer',
        'max_users' => 'integer',
        'has_analytics' => 'boolean',
        'has_iot' => 'boolean',
        'has_expert_support' => 'boolean',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(FarmSubscription::class, 'plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function isFree(): bool
    {
        return $this->monthly_price == 0 && $this->annual_price == 0;
    }

    public function getAnnualSavingsPercentage(): float
    {
        if ($this->monthly_price == 0) {
            return 0;
        }
        $yearlyMonthlyTotal = $this->monthly_price * 12;
        return round((($yearlyMonthlyTotal - $this->annual_price) / $yearlyMonthlyTotal) * 100, 1);
    }

    public function getPriceForCycle(string $cycle): float
    {
        return $cycle === 'annual' ? $this->annual_price : $this->monthly_price;
    }
}
