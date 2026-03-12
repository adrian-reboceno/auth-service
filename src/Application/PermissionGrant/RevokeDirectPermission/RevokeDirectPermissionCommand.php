<?php

namespace Application\PermissionGrant\RevokeDirectPermission;

use Domain\User\UserId;
use Domain\Permission\PermissionId;

final readonly class RevokeDirectPermissionCommand
{
    public function __construct(
        public UserId $targetUserId,
        public PermissionId $permissionId,
        public UserId $actingUserId
    ) {
    }
}
