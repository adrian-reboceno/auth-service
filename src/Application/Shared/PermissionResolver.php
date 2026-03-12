<?php

namespace Application\Shared;

use Domain\User\UserId;
use Domain\Role\RoleRepositoryInterface;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Domain\UserPermissionGrant\UserPermissionGrantRepositoryInterface;
use Domain\Services\PermissionCalculator;

final class PermissionResolver
{
    public function __construct(
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments,
        private readonly UserPermissionGrantRepositoryInterface $directGrants,
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionCalculator $calculator
    ) {
    }

    public function resolve(UserId $userId, bool $userIsActive): array
    {
        if (!$userIsActive) {
            return [];
        }

        $assignments = $this->roleAssignments->findByUserId($userId);

        if ($assignments->isEmpty()) {
            return []; // ADR-08: no roles → no permissions
        }

        // HIGH-03: single IN query — no N+1
        $roleIds = $assignments->map(fn($a) => $a->roleId())->toArray();
        $roles = $this->roles->findByIds($roleIds);

        $grants = $this->directGrants->findActiveByUserId($userId);

        return $this->calculator->compute(
            $assignments->all(),
            $roles->all(),
            $grants->all()
        );
    }
}
