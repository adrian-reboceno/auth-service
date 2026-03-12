<?php

namespace Domain\Permission;

use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    public function findById(PermissionId $id): ?Permission;
    
    /** @return Collection<int, Permission> */
    public function findAll(): Collection;

    public function save(Permission $permission): void;
}
