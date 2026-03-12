<?php

namespace Application\PermissionGrant\GrantDirectPermission;

use Domain\UserPermissionGrant\UserPermissionGrant;
use Domain\UserPermissionGrant\UserPermissionGrantId;
use Domain\UserPermissionGrant\UserPermissionGrantRepositoryInterface;
use Domain\User\UserRepositoryInterface;
use Domain\Permission\PermissionRepositoryInterface;

final class GrantDirectPermissionCommandHandler
{
    public function __construct(
        private readonly UserPermissionGrantRepositoryInterface $grants,
        private readonly UserRepositoryInterface $users,
        private readonly PermissionRepositoryInterface $permissions
    ) {
    }

    public function handle(GrantDirectPermissionCommand $command): void
    {
        // SEC-08 (SEC-01): Self-modification guard
        if ($command->targetUserId->value() === $command->actingUserId->value()) {
            throw new \DomainException('cannot_grant_permissions_to_self');
        }

        $user = $this->users->findById($command->targetUserId);
        if ($user === null || !$user->isActive()) {
            throw new \DomainException('user_not_found_or_inactive');
        }

        $permission = $this->permissions->findById($command->permissionId);
        if ($permission === null) {
            throw new \DomainException('permission_not_found');
        }

        $existing = $this->grants->findByUserAndPermission($command->targetUserId, $command->permissionId);

        if ($existing !== null) {
            if ($existing->status() === \Domain\UserPermissionGrant\GrantStatus::Active) {
                return; // Already active
            }
            // If it exists but is revoked/expired, ADR-04 / HIGH-05 states:
            // "An Expired/Revoked grant cannot be reactivated — a new grant must be created."
        }

        $grant = UserPermissionGrant::grant(
            new UserPermissionGrantId((string) \Illuminate\Support\Str::uuid()),
            $command->targetUserId,
            $command->permissionId,
            $command->expiresAt
        );

        $this->grants->save($grant);
    }
}
