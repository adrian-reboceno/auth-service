<?php

namespace Application\RoleAssignment\AssignRoleToUser;

use Domain\UserRoleAssignment\UserRoleAssignment;
use Domain\UserRoleAssignment\UserRoleAssignmentId;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Domain\User\UserRepositoryInterface;
use Domain\Role\RoleRepositoryInterface;

final class AssignRoleToUserCommandHandler
{
    public function __construct(
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments,
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(AssignRoleToUserCommand $command): void
    {
        // SEC-08: Self-modification guard
        if ($command->targetUserId->value() === $command->actingUserId->value()) {
            throw new \DomainException('cannot_assign_roles_to_self');
        }

        $user = $this->users->findById($command->targetUserId);
        if ($user === null || !$user->isActive()) {
            throw new \DomainException('user_not_found_or_inactive');
        }

        $role = $this->roles->findById($command->roleId);
        if ($role === null || !$role->isActive()) {
            throw new \DomainException('role_not_found_or_inactive');
        }

        $existing = $this->roleAssignments->findByUserAndRole($command->targetUserId, $command->roleId);
        if ($existing !== null) {
            return; // Already assigned
        }

        $assignment = UserRoleAssignment::assign(
            new UserRoleAssignmentId((string) \Illuminate\Support\Str::uuid()),
            $command->targetUserId,
            $command->roleId
        );

        $this->roleAssignments->save($assignment);
    }
}
