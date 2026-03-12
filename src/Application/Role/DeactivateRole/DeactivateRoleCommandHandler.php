<?php

namespace Application\Role\DeactivateRole;

use Domain\Role\RoleRepositoryInterface;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;

final class DeactivateRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments
    ) {
    }

    public function handle(DeactivateRoleCommand $command): void
    {
        $role = $this->roles->findById($command->roleId);

        if ($role === null) {
            throw new \DomainException('role_not_found');
        }

        if ($role->isSystem()) {
            throw new \DomainException('system_role_protected'); // Cannot delete/deactivate system role
        }

        $assignments = $this->roleAssignments->findByRoleId($command->roleId);
        if (!$assignments->isEmpty()) {
            throw new \DomainException('role_in_use'); // Cannot delete role with active assignments
        }

        $role->deactivate();
        $this->roles->save($role);
    }
}
