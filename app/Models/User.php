<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\FarmRole;
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

    // ==========================================
    // Role Helper Methods
    // ==========================================

    /**
     * Check if user is a super user (global admin)
     */
    public function isSuperUser(): bool
    {
        return $this->is_super_user === true;
    }

    /**
     * Get the user's role in the current farm
     */
    public function getCurrentFarmRole(): ?FarmRole
    {
        if (!$this->current_farm_id) {
            return null;
        }

        $farm = $this->farms()->where('farms.id', $this->current_farm_id)->first();

        if (!$farm) {
            return null;
        }

        return FarmRole::fromString($farm->pivot->role);
    }

    /**
     * Get the user's role string in the current farm
     */
    public function getCurrentFarmRoleString(): ?string
    {
        return $this->getCurrentFarmRole()?->value;
    }

    /**
     * Get the user's role in a specific farm
     */
    public function getRoleInFarm(Farm|string $farm): ?FarmRole
    {
        $farmId = $farm instanceof Farm ? $farm->id : $farm;
        $farmRecord = $this->farms()->where('farms.id', $farmId)->first();

        if (!$farmRecord) {
            return null;
        }

        return FarmRole::fromString($farmRecord->pivot->role);
    }

    /**
     * Check if user has at least the specified role in current farm
     * Super users always return true
     */
    public function hasRoleAtLeast(FarmRole $requiredRole): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $currentRole = $this->getCurrentFarmRole();

        if (!$currentRole) {
            return false;
        }

        return $currentRole->isAtLeast($requiredRole);
    }

    /**
     * Check if user has a specific role in current farm
     * Super users always return true
     */
    public function hasRole(FarmRole $role): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $currentRole = $this->getCurrentFarmRole();

        return $currentRole === $role;
    }

    /**
     * Check if user can access finance features in current farm
     */
    public function canAccessFinance(): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->canAccessFinance() ?? false;
    }

    /**
     * Check if user can modify finance data in current farm
     */
    public function canModifyFinance(): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->canModifyFinance() ?? false;
    }

    /**
     * Check if user can access operational features in current farm
     */
    public function canAccessOperations(): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->canAccessOperations() ?? false;
    }

    /**
     * Check if user can modify operational data in current farm
     */
    public function canModifyOperations(): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->canModifyOperations() ?? false;
    }

    /**
     * Check if user can manage farm members
     */
    public function canManageMembers(): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->canManageMembers() ?? false;
    }

    /**
     * Check if user can access settings
     */
    public function canAccessSettings(): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->canAccessSettings() ?? false;
    }

    /**
     * Check if user is view-only (investor)
     */
    public function isViewOnly(): bool
    {
        if ($this->isSuperUser()) {
            return false;
        }

        $role = $this->getCurrentFarmRole();

        return $role?->isViewOnly() ?? false;
    }

    /**
     * Check if user can access a specific farm
     * Super users can access any farm
     */
    public function canAccessFarm(Farm|string $farm): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $farmId = $farm instanceof Farm ? $farm->id : $farm;

        return $this->farms()->where('farms.id', $farmId)->exists();
    }
}
