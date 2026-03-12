<?php

namespace Domain\Role\Events;

use Domain\Role\RoleId;
use Domain\Role\RoleName;

final readonly class RoleCreated
{
    public function __construct(public RoleId $roleId, public RoleName $roleName)
    {
    }
}
