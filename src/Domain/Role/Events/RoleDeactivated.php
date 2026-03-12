<?php

namespace Domain\Role\Events;

use Domain\Role\RoleId;

final readonly class RoleDeactivated
{
    public function __construct(public RoleId $roleId)
    {
    }
}
