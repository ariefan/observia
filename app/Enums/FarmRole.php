<?php

namespace App\Enums;

/**
 * Farm Role Enum
 *
 * Defines the roles a user can have within a specific farm.
 * Note: Super Users have global access and don't need farm roles.
 */
enum FarmRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case FARMER = 'farmer';
    case INVESTOR = 'investor';

    /**
     * Get human-readable label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::OWNER => 'Pemilik',
            self::ADMIN => 'Administrator',
            self::FARMER => 'Peternak',
            self::INVESTOR => 'Investor',
        };
    }

    /**
     * Get role description
     */
    public function description(): string
    {
        return match($this) {
            self::OWNER => 'Kontrol penuh atas peternakan termasuk manajemen anggota dan penghapusan',
            self::ADMIN => 'Mengelola operasional peternakan dan anggota tim',
            self::FARMER => 'Mengelola ternak, kesehatan, produktivitas, dan inventaris',
            self::INVESTOR => 'Akses hanya baca untuk laporan dan data keuangan',
        };
    }

    /**
     * Get all roles as array for dropdowns
     */
    public static function options(): array
    {
        return array_map(fn($role) => [
            'value' => $role->value,
            'label' => $role->label(),
            'description' => $role->description(),
        ], self::cases());
    }

    /**
     * Check if this role can manage farm members
     */
    public function canManageMembers(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }

    /**
     * Check if this role can delete the farm
     */
    public function canDeleteFarm(): bool
    {
        return $this === self::OWNER;
    }

    /**
     * Check if this role can access finance features (approve payments, etc)
     */
    public function canAccessFinance(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::INVESTOR]);
    }

    /**
     * Check if this role can modify finance data (not just view)
     */
    public function canModifyFinance(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }

    /**
     * Check if this role can access operational features (livestock, health, etc)
     */
    public function canAccessOperations(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::FARMER]);
    }

    /**
     * Check if this role can modify operational data
     */
    public function canModifyOperations(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::FARMER]);
    }

    /**
     * Check if this role can access settings
     */
    public function canAccessSettings(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }

    /**
     * Check if this role has view-only access
     */
    public function isViewOnly(): bool
    {
        return $this === self::INVESTOR;
    }

    /**
     * Get permission level (higher = more permissions)
     */
    public function permissionLevel(): int
    {
        return match($this) {
            self::OWNER => 100,
            self::ADMIN => 80,
            self::FARMER => 50,
            self::INVESTOR => 20,
        };
    }

    /**
     * Check if this role is equal to or higher than another role
     */
    public function isAtLeast(FarmRole $role): bool
    {
        return $this->permissionLevel() >= $role->permissionLevel();
    }

    /**
     * Create from string value, with fallback
     */
    public static function fromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
