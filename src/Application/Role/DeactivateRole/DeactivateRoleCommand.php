<?php

namespace Application\Role\DeactivateRole;

use Domain\Role\RoleId;

final readonly class DeactivateRoleCommand
{
    public function __construct(
        public RoleId $roleId
    ) {
    }
}
