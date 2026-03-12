<?php

namespace Domain\UserRoleAssignment\Events;

use Domain\User\UserId;
use Domain\Role\RoleId;

final readonly class RoleRevokedFromUser
{
    public function __construct(public UserId $userId, public RoleId $roleId)
    {
    }
}
