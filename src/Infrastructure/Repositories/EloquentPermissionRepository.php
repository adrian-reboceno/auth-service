<?php

namespace Infrastructure\Repositories;

use Domain\Permission\PermissionRepositoryInterface;
use Domain\Permission\Permission as DomainPermission;
use Domain\Permission\PermissionId;
use Domain\Permission\PermissionName;
use App\Models\Permission as EloquentPermission;
use Illuminate\Support\Collection;

final class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function findById(PermissionId $id): ?DomainPermission
    {
        $model = EloquentPermission::find($id->value());
        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }
    
    public function findAll(): Collection
    {
        return EloquentPermission::all()->map(fn($model) => $this->toDomain($model));
    }

    public function save(DomainPermission $permission): void
    {
        $model = EloquentPermission::find($permission->id()->value());
        if (!$model) {
            $model = new EloquentPermission();
            $model->id = $permission->id()->value();
        }

        $model->name = $permission->name()->value();
        $model->description = $permission->description();
        $model->save();

        foreach ($permission->pullDomainEvents() as $event) {
            event($event);
        }
    }

    private function toDomain(EloquentPermission $model): DomainPermission
    {
        return new DomainPermission(
            new PermissionId($model->id),
            new PermissionName($model->name),
            $model->description
        );
    }
}
