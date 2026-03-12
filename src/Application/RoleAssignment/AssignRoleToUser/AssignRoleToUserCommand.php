<?php

namespace Application\RoleAssignment\AssignRoleToUser;

use Domain\User\UserId;
use Domain\Role\RoleId;

final readonly class AssignRoleToUserCommand
{
    public function __construct(
        public UserId $targetUserId,
        public RoleId $roleId,
        public UserId $actingUserId
    ) {
    }
}
