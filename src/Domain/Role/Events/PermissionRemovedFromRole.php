<?php

namespace Domain\Role\Events;

use Domain\Role\RoleId;
use Domain\Permission\PermissionId;

final readonly class PermissionRemovedFromRole
{
    public function __construct(public RoleId $roleId, public PermissionId $permissionId)
    {
    }
}
