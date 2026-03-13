<?php

namespace Infrastructure\Repositories;

use Domain\Role\RoleRepositoryInterface;
use Domain\Role\Role as DomainRole;
use Domain\Role\RoleId;
use Domain\Role\RoleName;
use Domain\Permission\PermissionId;
use Domain\Permission\PermissionName;
use App\Models\Role as EloquentRole;
use Illuminate\Support\Collection;

final class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function findById(RoleId $id): ?DomainRole
    {
        $model = EloquentRole::with('permissions')->find($id->value());
        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): Collection
    {
        return EloquentRole::with('permissions')
            ->get()
            ->map(fn($model) => $this->toDomain($model));
    }

    /** HIGH-03: Single IN query — no N+1 */
    public function findByIds(array $roleIds): Collection
    {
        if (empty($roleIds)) return collect();

        $ids = array_map(fn(RoleId $id) => $id->value(), $roleIds);

        return EloquentRole::with('permissions')
            ->whereIn('id', $ids)
            ->get()
            ->map(fn($model) => $this->toDomain($model));
    }

    public function save(DomainRole $role): void
    {
        $model = EloquentRole::find($role->id()->value()) ?? new EloquentRole();
        $model->id        = $role->id()->value();
        $model->name      = $role->name()->value();
        $model->is_active = $role->isActive();
        $model->is_system = $role->isSystem();
        $model->description = $role->description();
        $model->save();

        // Sync permissions by name
        $permissionIds = \App\Models\Permission::whereIn("name",
            array_map(fn($n) => $n->value(), $role->permissionNames())
        )->pluck("id")->toArray();
        $model->permissions()->sync($permissionIds);

        foreach ($role->pullDomainEvents() as $event) {
            event($event);
        }
    }

    private function toDomain(EloquentRole $model): DomainRole
    {
        $permissionNames = $model->permissions
            ->map(fn($p) => new PermissionName($p->name))
            ->toArray();

        return (new DomainRole(
            new RoleId($model->id),
            new RoleName($model->name),
            $permissionNames,
            $model->is_active,
            $model->is_system
        ))->withDescription($model->description);
    }
}
