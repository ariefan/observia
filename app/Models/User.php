<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasFarmRoles;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasFarmRoles, HasUuids, Auditable;

    // Sensitive fields to exclude from auditing
    protected $auditExclude = ['password', 'remember_token', 'email_verified_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'is_super_user',
        'profile_photo_url',
        'current_farm_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_user' => 'boolean',
        ];
    }
    
    // public function farms()
    // {
    //     return $this->belongsToMany(Farm::class);
    // }

    public function farms()
    {
        return $this->belongsToMany(Farm::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function currentFarm()
    {
        return $this->belongsTo(Farm::class, 'current_farm_id');
    }
}
