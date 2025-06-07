<?php
namespace App\Traits;

use App\Services\FarmRoleService;

trait HasFarmRoles
{
    public function assignFarmRole(string $baseRole)
    {
        return app(FarmRoleService::class)->assignFarmRole($this, $baseRole);
    }

    public function assignFarmPermission(string $basePermission)
    {
        return app(FarmRoleService::class)->assignFarmPermission($this, $basePermission);
    }

    public function hasFarmRole(string $baseRole): bool
    {
        return app(FarmRoleService::class)->hasFarmRole($this, $baseRole);
    }

    public function hasFarmPermission(string $basePermission): bool
    {
        return app(FarmRoleService::class)->hasFarmPermission($this, $basePermission);
    }

    public function getFarmRoles(): array
    {
        $farm = $this->currentFarm ?? null;
        $prefix = $farm ? "@farm-{$farm->id}" : null;

        // Get all role names for this user scoped by farm
        $roles = \DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('user_role.user_id', $this->id)
            ->when($farm, function ($query) use ($farm) {
                $query->where('roles.farm_id', $farm->id);
            }, function ($query) {
                $query->whereNull('roles.farm_id');
            })
            ->pluck('roles.name')
            ->toArray();

        return $roles;
    }
}
