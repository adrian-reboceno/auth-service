<?php

namespace Domain\UserPermissionGrant;

use Domain\User\UserId;
use Domain\Permission\PermissionId;
use Illuminate\Support\Collection;

interface UserPermissionGrantRepositoryInterface
{
    /** @return Collection<int, UserPermissionGrant> */
    public function findActiveByUserId(UserId $userId): Collection;

    public function findByUserAndPermission(UserId $userId, PermissionId $permissionId): ?UserPermissionGrant;

    public function save(UserPermissionGrant $grant): void;
}
