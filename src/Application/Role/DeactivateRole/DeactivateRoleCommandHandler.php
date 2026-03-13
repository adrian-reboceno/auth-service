<?php

namespace Application\Role\DeactivateRole;

use Domain\Role\RoleRepositoryInterface;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;

final class DeactivateRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments
    ) {}

    public function handle(DeactivateRoleCommand $command): void
    {
        $role = $this->roles->findById($command->roleId);
        if ($role === null) {
            throw new \DomainException("role_not_found");
        }

        if ($role->isSystem()) {
            throw new \DomainException("system_role_protected");
        }

        if ($this->roleAssignments->hasAssignments($command->roleId)) {
            throw new \DomainException("role_in_use");
        }

        $role->deactivate();
        $this->roles->save($role);
    }
}
