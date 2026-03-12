<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users:create'      => 'Create new users',
            'users:read'        => 'View users',
            'users:update'      => 'Update users',
            'users:deactivate'  => 'Deactivate users',
            'roles:assign'      => 'Assign roles to users',
            'permissions:grant' => 'Grant direct permissions',
        ];

        foreach ($permissions as $name => $description) {
            [$resource, $action] = explode(':', $name);
            $exists = DB::table('permissions')->where('name', $name)->count() > 0;
            if (!$exists) {
                DB::table('permissions')->insert([
                    'id'          => (string) Str::uuid(),
                    'name'        => $name,
                    'resource'    => $resource,
                    'action'      => $action,
                    'description' => $description,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id', 'name');

        $roleExists = DB::table('roles')->where('name', 'super_admin')->count() > 0;
        if (!$roleExists) {
            DB::table('roles')->insert([
                'id'          => (string) Str::uuid(),
                'name'        => 'super_admin',
                'description' => 'System root role with full access',
                'is_system'   => true,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $superAdminRoleId = DB::table('roles')->where('name', 'super_admin')->value('id');

        foreach ($permissionIds as $permName => $permId) {
            $assigned = DB::table('role_permissions')
                ->where('role_id', $superAdminRoleId)
                ->where('permission_id', $permId)
                ->count() > 0;
            if (!$assigned) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $superAdminRoleId,
                    'permission_id' => $permId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
