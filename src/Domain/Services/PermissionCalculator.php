<?php

namespace Domain\Services;

use Domain\Role\Role;
use Domain\UserPermissionGrant\UserPermissionGrant;
use Domain\UserPermissionGrant\GrantStatus;
use Domain\UserRoleAssignment\UserRoleAssignment;

final class PermissionCalculator
{
    /**
     * @param  UserRoleAssignment[]  $assignments
     * @param  Role[]                $roles
     * @param  UserPermissionGrant[] $grants
     * @return string[]              permission names e.g. ['users:create', 'roles:assign']
     */
    public function compute(
        array $assignments,
        array $roles,
        array $grants
    ): array {
        $fromRoles = collect($roles)
            ->flatMap(fn(Role $role) => $role->activePermissionNames())
            ->unique();

        $direct = collect($grants)
            ->filter(fn(UserPermissionGrant $g) => $g->status() === GrantStatus::Active)
            ->map(fn(UserPermissionGrant $g) => $g->permissionName()->value());

        return $fromRoles
            ->merge($direct)
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
