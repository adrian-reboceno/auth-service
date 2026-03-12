<?php

namespace Application\Role\AddPermissionToRole;

use Domain\Role\RoleId;
use Domain\Permission\PermissionId;

final readonly class AddPermissionToRoleCommand
{
    public function __construct(
        public RoleId $roleId,
        public PermissionId $permissionId
    ) {
    }
}
