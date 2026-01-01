<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\Auditable;

class Farm extends Model
{
    /** @use HasFactory<\Database\Factories\FarmFactory> */
    use HasFactory, HasUuids, Auditable;

    // Sensitive fields to exclude from auditing
    protected $auditExclude = ['password', 'remember_token'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'picture',
        'owner',
        'email',
        'phone',
        'city_id',
        'latlong',
        'user_id',
        'farm_type',
        'milk_pricing',
        'milk_supplier_info',
    ];

    protected $casts = [
        'latlong' => 'array',
        'milk_pricing' => 'array',
        'milk_supplier_info' => 'array',
    ];

    /**
     * The users that belong to the farm.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get the owner (main user) of the farm.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the province through the city.
     */
    public function province()
    {
        return $this->hasOneThrough(Province::class, City::class, 'id', 'id', 'city_id', 'province_id');
    }

    /**
     * Get the livestocks for the farm.
     */
    public function livestocks()
    {
        return $this->hasMany(Livestock::class);
    }

    /**
     * Get the herds for the farm.
     */
    public function herds()
    {
        return $this->hasMany(Herd::class);
    }

    /**
     * Get the rations for the farm.
     */
    public function rations()
    {
        return $this->hasMany(Ration::class);
    }

    /**
     * Get the milk batches for the farm.
     */
    public function milkBatches()
    {
        return $this->hasMany(MilkBatch::class);
    }

    /**
     * Get the cheese productions for the farm.
     */
    public function cheeseProductions()
    {
        return $this->hasMany(CheeseProduction::class);
    }

    /**
     * Get the milk payments for the farm.
     */
    public function milkPayments()
    {
        return $this->hasMany(MilkPayment::class);
    }

    /**
     * Get all subscriptions for the farm.
     */
    public function subscriptions()
    {
        return $this->hasMany(FarmSubscription::class);
    }

    /**
     * Get the active subscription for the farm.
     */
    public function activeSubscription()
    {
        return $this->hasOne(FarmSubscription::class)->where('status', 'active')->latest();
    }

    /**
     * Get all subscription invoices for the farm.
     */
    public function subscriptionInvoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * Check if farm has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->active()->exists();
    }

    /**
     * Get the current subscription plan.
     */
    public function getCurrentPlan(): ?SubscriptionPlan
    {
        $subscription = $this->activeSubscription;
        return $subscription?->plan;
    }

    /**
     * Create default medicine inventory items for this farm.
     */
    public function createDefaultMedicines(): void
    {
        // Get the medicine category and units
        $medicineCategory = InventoryCategory::where('name', 'Obat-obatan')->first();
        if (!$medicineCategory) {
            return; // Skip if no medicine category exists
        }

        $units = InventoryUnit::whereIn('symbol', ['tablet', 'kapsul', 'vial', 'tube', 'ml', 'sachet'])->get()->keyBy('symbol');

        $defaultMedicines = [
            ['name' => 'Neomisin', 'sku' => 'QA07AA02', 'unit' => 'tablet', 'description' => 'Antibiotic for intestinal infections'],
            ['name' => 'Doksisiklin', 'sku' => 'QJ01AA02', 'unit' => 'tablet', 'description' => 'Broad spectrum antibiotic'],
            ['name' => 'Ampisilin', 'sku' => 'QJ01CA01', 'unit' => 'vial', 'description' => 'Penicillin antibiotic'],
            ['name' => 'Penoksimetilpenisilin', 'sku' => 'QJ01CE02', 'unit' => 'tablet', 'description' => 'Oral penicillin'],
            ['name' => 'Eritromisin', 'sku' => 'QJ01DA02', 'unit' => 'tablet', 'description' => 'Macrolide antibiotic'],
            ['name' => 'Trimetoprim', 'sku' => 'QJ01EA01', 'unit' => 'tablet', 'description' => 'Sulfonamide antibiotic'],
            ['name' => 'Klindamisin', 'sku' => 'QJ01FF01', 'unit' => 'vial', 'description' => 'Lincosamide antibiotic'],
            ['name' => 'Kombinasi Garam', 'sku' => 'QA02AD01', 'unit' => 'sachet', 'description' => 'Electrolyte for dehydration'],
            ['name' => 'Atropin', 'sku' => 'QA03AA04', 'unit' => 'vial', 'description' => 'Antispasmodic'],
            ['name' => 'Bisakodil', 'sku' => 'QA06AB58', 'unit' => 'tablet', 'description' => 'Stimulant laxative'],
            ['name' => 'Dinoprost', 'sku' => 'QG04BD04', 'unit' => 'vial', 'description' => 'Prostaglandin for reproduction'],
            ['name' => 'Oktreotide', 'sku' => 'QH01CB02', 'unit' => 'vial', 'description' => 'Somatostatin analog'],
            ['name' => 'Kloramfenikol', 'sku' => 'QJ01BA01', 'unit' => 'ml', 'description' => 'Chloramphenicol antibiotic'],
            ['name' => 'Ketoprofen', 'sku' => 'QM01AE03', 'unit' => 'vial', 'description' => 'Anti-inflammatory'],
            ['name' => 'Halotan', 'sku' => 'QN01AF01', 'unit' => 'ml', 'description' => 'General anesthetic'],
            ['name' => 'Ivermektin', 'sku' => 'QP51AG02', 'unit' => 'ml', 'description' => 'Antiparasitic'],
            ['name' => 'Albendazol', 'sku' => 'QP52AC11', 'unit' => 'tablet', 'description' => 'Anthelmintic'],
        ];

        foreach ($defaultMedicines as $medicine) {
            $unit = $units->get($medicine['unit']);
            if (!$unit) continue;

            // Check if medicine already exists for this farm
            $exists = InventoryItem::where('farm_id', $this->id)
                ->where('name', $medicine['name'])
                ->where('category_id', $medicineCategory->id)
                ->exists();

            if (!$exists) {
                InventoryItem::create([
                    'farm_id' => $this->id,
                    'category_id' => $medicineCategory->id,
                    'unit_id' => $unit->id,
                    'name' => $medicine['name'],
                    'description' => $medicine['description'],
                    'sku' => $medicine['sku'],
                    'minimum_stock' => 1,
                    'current_stock' => 0,
                    'track_expiry' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
