<?php

namespace Domain\UserPermissionGrant\Events;

use Domain\User\UserId;
use Domain\Permission\PermissionId;
use Carbon\CarbonImmutable;

final readonly class DirectPermissionGranted
{
    public function __construct(
        public UserId $userId,
        public PermissionId $permissionId,
        public ?CarbonImmutable $expiresAt
    ) {
    }
}
