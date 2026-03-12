<?php

namespace Application\Role\RemovePermissionFromRole;

use Domain\Role\RoleRepositoryInterface;

final class RemovePermissionFromRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(RemovePermissionFromRoleCommand $command): void
    {
        $role = $this->roles->findById($command->roleId);
        if ($role === null) {
            throw new \DomainException('role_not_found');
        }

        $role->removePermission($command->permissionId);
        $this->roles->save($role);
    }
}
