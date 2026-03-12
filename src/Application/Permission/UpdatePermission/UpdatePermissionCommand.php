<?php

namespace Application\Permission\UpdatePermission;

use Domain\Permission\PermissionId;

final class UpdatePermissionCommand
{
    public function __construct(
        public readonly PermissionId $id,
        public readonly ?string $description
    ) {}
}
