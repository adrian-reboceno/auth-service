<?php

namespace Application\Permission\UpdatePermission;

use Domain\Permission\PermissionRepositoryInterface;

final class UpdatePermissionCommandHandler
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions
    ) {}

    public function handle(UpdatePermissionCommand $command): void
    {
        $permission = $this->permissions->findById($command->id);
        if ($permission === null) {
            throw new \DomainException("permission_not_found");
        }
        $permission->updateDescription($command->description);
        $this->permissions->save($permission);
    }
}
