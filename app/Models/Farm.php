<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Farm extends Model
{
    /** @use HasFactory<\Database\Factories\FarmFactory> */
    use HasFactory, HasUuids;

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
    ];

    protected $casts = [
        'latlong' => 'array',
    ];  
    
    /**
     * The users that belong to the farm.
     */
    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'farm_user', 'farm_id', 'user_id');
    // }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role')
                    ->withTimestamps();
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
}
