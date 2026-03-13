<?php

namespace Application\Role\RemovePermissionFromRole;

use Domain\Role\RoleRepositoryInterface;
use Domain\Permission\PermissionRepositoryInterface;

final class RemovePermissionFromRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionRepositoryInterface $permissions
    ) {}

    public function handle(RemovePermissionFromRoleCommand $command): void
    {
        $role = $this->roles->findById($command->roleId);
        if ($role === null) {
            throw new \DomainException("role_not_found");
        }

        $permission = $this->permissions->findById($command->permissionId);
        if ($permission === null) {
            throw new \DomainException("permission_not_found");
        }

        $role->removePermissionByName($permission->name());
        $this->roles->save($role);
    }
}
