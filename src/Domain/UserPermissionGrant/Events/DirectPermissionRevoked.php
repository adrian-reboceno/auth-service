<?php

namespace Domain\UserPermissionGrant\Events;

use Domain\User\UserId;
use Domain\Permission\PermissionId;

final readonly class DirectPermissionRevoked
{
    public function __construct(
        public UserId $userId,
        public PermissionId $permissionId
    ) {
    }
}
