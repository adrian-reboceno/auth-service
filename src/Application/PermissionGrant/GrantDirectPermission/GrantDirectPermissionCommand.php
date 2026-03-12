<?php

namespace Application\PermissionGrant\GrantDirectPermission;

use Domain\User\UserId;
use Domain\Permission\PermissionId;

final readonly class GrantDirectPermissionCommand
{
    public function __construct(
        public UserId $targetUserId,
        public PermissionId $permissionId,
        public UserId $actingUserId,
        public ?\DateTimeImmutable $expiresAt = null
    ) {
    }
}
