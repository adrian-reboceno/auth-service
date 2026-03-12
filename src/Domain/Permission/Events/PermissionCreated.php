<?php

namespace Domain\Permission\Events;

use Domain\Permission\PermissionId;
use Domain\Permission\PermissionName;

final readonly class PermissionCreated
{
    public function __construct(
        public PermissionId $permissionId,
        public PermissionName $permissionName
    ) {
    }
}
