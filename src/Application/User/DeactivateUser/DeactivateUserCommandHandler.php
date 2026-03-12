<?php

namespace Application\User\DeactivateUser;

use Domain\User\UserRepositoryInterface;
use Domain\Role\RoleRepositoryInterface;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Application\Shared\SecurityRevoker;

final class DeactivateUserCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly SecurityRevoker $securityRevoker,
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments,
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(DeactivateUserCommand $command): void
    {
        $user = $this->users->findById($command->userId);

        if ($user === null) {
            throw new \DomainException('user_not_found');
        }

        if (!$user->isActive()) {
            return;
        }

        // SEC-02: the system MUST NOT allow the last active user with the root super_admin role to be deactivated
        // Check if user is an admin
        $assignments = $this->roleAssignments->findByUserId($user->id());
        $isAdmin = false;
        
        foreach ($assignments as $assignment) {
            $role = $this->roles->findById($assignment->roleId());
            if ($role !== null && $role->name()->value() === 'super_admin') {
                $isAdmin = true;
                break;
            }
        }

        if ($isAdmin) {
            $activeAdminsCount = $this->users->countActiveAdmins();
            if ($activeAdminsCount <= 1) {
                throw new \DomainException('cannot_deactivate_last_active_admin');
            }
        }

        // 1. Deactivate
        $user->deactivate();
        $this->users->save($user);

        // 2. Clear sessions
        $this->securityRevoker->revokeAllSessions($user->id());
    }
}
