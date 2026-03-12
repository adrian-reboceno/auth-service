<?php

namespace Domain\Role;

use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function findById(RoleId $id): ?Role;
    
    /** @return Collection<int, Role> */
    public function findAll(): Collection;

    /** 
     * HIGH-03: single IN query
     * @param RoleId[] $roleIds 
     * @return Collection<int, Role>
     */
    public function findByIds(array $roleIds): Collection;

    public function save(Role $role): void;
}
