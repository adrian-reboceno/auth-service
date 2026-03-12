<?php

namespace Application\Role\CreateRole;

use Domain\Role\Role;
use Domain\Role\RoleId;
use Domain\Role\RoleName;
use Domain\Role\RoleRepositoryInterface;
use Illuminate\Support\Str;

final class CreateRoleCommandHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(CreateRoleCommand $command): void
    {
        $role = collect($this->roles->findAll())->first(fn(Role $r) => $r->name()->value() === $command->name);
        if ($role !== null) {
            throw new \DomainException('role_already_exists');
        }

        $newRole = Role::create(
            new RoleId((string) Str::uuid()),
            RoleName::fromString($command->name),
            $command->description,
            $command->isSystem
        );

        $this->roles->save($newRole);
    }
}
