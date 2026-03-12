<?php

namespace Application\Role\RemovePermissionFromRole;

use Domain\Role\RoleId;
use Domain\Permission\PermissionId;

final readonly class RemovePermissionFromRoleCommand
{
    public function __construct(
        public RoleId $roleId,
        public PermissionId $permissionId
    ) {
    }
}
