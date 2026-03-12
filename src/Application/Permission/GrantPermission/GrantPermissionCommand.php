<?php

namespace Application\Permission\GrantPermission;

use Domain\User\UserId;
use Domain\Permission\PermissionId;
use Carbon\CarbonImmutable;

final readonly class GrantPermissionCommand
{
    public function __construct(
        public UserId $targetUserId,
        public PermissionId $permissionId,
        public UserId $actingUserId,
        public ?CarbonImmutable $expiresAt = null
    ) {
    }
}
