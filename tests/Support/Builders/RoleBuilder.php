<?php

namespace Tests\Support\Builders;

use Domain\Role\Role;
use Domain\Role\RoleId;
use Domain\Role\RoleName;
use Domain\Permission\PermissionId;
use Illuminate\Support\Str;

final class RoleBuilder
{
    private array $permissionNames = [];

    public static function withPermissions(array $names): Role
    {
        $builder = new self();
        $builder->permissionNames = $names;
        return $builder->build();
    }

    public function build(): Role
    {
        // For testing, we mock PermissionId via MD5 of name
        $permissionIds = array_map(function (string $name) {
            $uuid = substr(md5($name), 0, 8) . '-0000-0000-0000-000000000000';
            return new PermissionId($uuid);
        }, $this->permissionNames);

        return new Role(
            new RoleId((string) Str::uuid()),
            new RoleName('test_role_' . uniqid()),
            $permissionIds,
            true, // is_active
            false // is_system
        );
    }
}
