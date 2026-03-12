<?php

namespace Application\Permission\CreatePermission;

final class CreatePermissionCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description
    ) {}
}
