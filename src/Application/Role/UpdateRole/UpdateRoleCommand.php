<?php

namespace Application\Role\UpdateRole;

use Domain\Role\RoleId;

final readonly class UpdateRoleCommand
{
    public function __construct(
        public RoleId $roleId,
        public string $name,
        public ?string $description = null
    ) {
    }
}
