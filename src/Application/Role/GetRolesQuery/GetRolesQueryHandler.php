<?php

namespace Application\Role\GetRolesQuery;

use Domain\Role\RoleRepositoryInterface;
use Illuminate\Support\Collection;

final class GetRolesQueryHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(GetRolesQuery $query): Collection
    {
        // Simple implementation, could be optimized at infrastructure layer
        $roles = $this->roles->findAll();

        if ($query->isActive !== null) {
            $roles = $roles->filter(fn($r) => $r->isActive() === $query->isActive);
        }

        if ($query->search !== null && $query->search !== '') {
            $search = strtolower($query->search);
            $roles = $roles->filter(fn($r) => 
                str_contains(strtolower($r->name()->value()), $search) ||
                ($r->description() && str_contains(strtolower($r->description()), $search))
            );
        }

        return $roles->values();
    }
}
