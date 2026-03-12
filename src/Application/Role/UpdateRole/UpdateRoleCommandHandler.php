<?php

namespace Application\Role\UpdateRole;

use Domain\Role\RoleName;
use Domain\Role\RoleRepositoryInterface;

final class UpdateRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(UpdateRoleCommand $command): void
    {
        $role = $this->roles->findById($command->roleId);

        if ($role === null) {
            throw new \DomainException('role_not_found');
        }

        if ($role->isSystem() && $role->name()->value() !== $command->name) {
            throw new \DomainException('system_role_protected');
        }

        // Check name uniqueness if changed
        if ($role->name()->value() !== $command->name) {
            $existing = collect($this->roles->findAll())->first(fn($r) => $r->name()->value() === $command->name);
            if ($existing !== null) {
                throw new \DomainException('role_already_exists');
            }
        }

        $role->rename(RoleName::fromString($command->name));
        $role->updateDescription($command->description);

        $this->roles->save($role);
    }
}
