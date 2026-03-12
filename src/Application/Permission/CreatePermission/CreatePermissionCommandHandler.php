<?php

namespace Application\Permission\CreatePermission;

use Domain\Permission\Permission;
use Domain\Permission\PermissionId;
use Domain\Permission\PermissionName;
use Domain\Permission\PermissionRepositoryInterface;
use Illuminate\Support\Str;

final class CreatePermissionCommandHandler
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions
    ) {}

    public function handle(CreatePermissionCommand $command): string
    {
        $id = new PermissionId((string) Str::uuid());
        $permission = Permission::create($id, new PermissionName($command->name), $command->description);
        $this->permissions->save($permission);
        return $id->value();
    }
}
