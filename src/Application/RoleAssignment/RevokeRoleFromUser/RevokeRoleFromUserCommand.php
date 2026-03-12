<?php

namespace Application\RoleAssignment\RevokeRoleFromUser;

use Domain\User\UserId;
use Domain\Role\RoleId;

final readonly class RevokeRoleFromUserCommand
{
    public function __construct(
        public UserId $targetUserId,
        public RoleId $roleId,
        public UserId $actingUserId
    ) {
    }
}
