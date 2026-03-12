<?php

namespace Domain\UserRoleAssignment;

use Domain\User\UserId;
use Domain\Role\RoleId;
use Illuminate\Support\Collection;

interface UserRoleAssignmentRepositoryInterface
{
    /** @return Collection<int, UserRoleAssignment> */
    public function findByUserId(UserId $userId): Collection;

    public function findByUserAndRole(UserId $userId, RoleId $roleId): ?UserRoleAssignment;

    public function save(UserRoleAssignment $assignment): void;

    public function delete(UserRoleAssignment $assignment): void;
    
    public function hasAssignments(RoleId $roleId): bool;
}
