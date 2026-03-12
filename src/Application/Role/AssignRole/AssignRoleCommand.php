<?php

namespace Application\Role\AssignRole;

use Domain\User\UserId;
use Domain\Role\RoleId;

final readonly class AssignRoleCommand
{
    public function __construct(
        public UserId $targetUserId,
        public RoleId $roleId,
        public UserId $actingUserId
    ) {
    }
}
