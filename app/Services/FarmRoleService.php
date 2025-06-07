<?php
namespace App\Services;

use App\Models\User;
use App\Models\Farm;
use Illuminate\Support\Facades\DB;

class FarmRoleService
{
    public function getFarm(User $user): ?Farm
    {
        return $user->currentFarm ?? null;
    }

    protected function scopedName(string $base, ?Farm $farm): string
    {
        return $farm ? "{$base}@farm-{$farm->id}" : $base;
    }

    public function assignFarmRole(User $user, string $baseRole)
    {
        $farm = $this->getFarm($user);
        $roleName = $this->scopedName($baseRole, $farm);

        // Find or create role manually
        $role = DB::table('roles')->where('name', $roleName)
            ->where('farm_id', $farm?->id)
            ->first();

        if (!$role) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $roleName,
                'farm_id' => $farm?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $role->id;
        }

        // Assign role to user scoped by farm
        DB::table('user_role')->updateOrInsert(
            [
                'user_id' => $user->id,
                'role_id' => $roleId,
                'farm_id' => $farm?->id,
            ],
            ['updated_at' => now()]
        );

        return $user;
    }

    public function assignFarmPermission(User $user, string $basePermission)
    {
        $farm = $this->getFarm($user);
        $permName = $this->scopedName($basePermission, $farm);

        // Find or create permission manually
        $permission = DB::table('permissions')->where('name', $permName)->first();

        if (!$permission) {
            DB::table('permissions')->insert([
                'name' => $permName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Permissions can be assigned to roles only, so
        // You need another method to assign permission to roles (optional)

        return $user;
    }

    public function hasFarmRole(User $user, string $baseRole): bool
    {
        $farm = $this->getFarm($user);
        $roleName = $this->scopedName($baseRole, $farm);

        return DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('user_role.user_id', $user->id)
            ->where('roles.name', $roleName)
            ->where('roles.farm_id', $farm?->id)
            ->exists();
    }

    public function hasFarmPermission(User $user, string $basePermission): bool
    {
        $farm = $this->getFarm($user);
        $permName = $this->scopedName($basePermission, $farm);

        // Get all role_ids for user in this farm
        $roleIds = DB::table('user_role')
            ->where('user_id', $user->id)
            ->where('farm_id', $farm?->id)
            ->pluck('role_id');

        if ($roleIds->isEmpty()) {
            return false;
        }

        // Check if any role has this permission
        return DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->whereIn('role_permission.role_id', $roleIds)
            ->where('permissions.name', $permName)
            ->exists();
    }
}
