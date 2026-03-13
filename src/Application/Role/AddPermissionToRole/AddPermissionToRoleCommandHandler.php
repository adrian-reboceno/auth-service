<?php

namespace Application\Role\AddPermissionToRole;

use Domain\Role\RoleRepositoryInterface;
use Domain\Permission\PermissionRepositoryInterface;

final class AddPermissionToRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionRepositoryInterface $permissions
    ) {
    }

    public function handle(AddPermissionToRoleCommand $command): void
    {
        $role = $this->roles->findById($command->roleId);
        if ($role === null) {
            throw new \DomainException('role_not_found');
        }

        $permission = $this->permissions->findById($command->permissionId);
        if ($permission === null) {
            throw new \DomainException('permission_not_found');
        }

        $role->addPermissionByName($permission->name());
        $this->roles->save($role);
    }
}
