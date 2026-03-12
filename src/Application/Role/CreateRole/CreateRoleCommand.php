<?php

namespace Application\Role\CreateRole;

final readonly class CreateRoleCommand
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isSystem = false
    ) {
    }
}
