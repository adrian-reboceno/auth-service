<?php

namespace Application\PermissionGrant\RevokeDirectPermission;

use Domain\UserPermissionGrant\UserPermissionGrantRepositoryInterface;
use Domain\User\UserRepositoryInterface;

final class RevokeDirectPermissionCommandHandler
{
    public function __construct(
        private readonly UserPermissionGrantRepositoryInterface $grants,
        private readonly UserRepositoryInterface $users
    ) {
    }

    public function handle(RevokeDirectPermissionCommand $command): void
    {
        // SEC-08 (SEC-01): Self-modification guard
        if ($command->targetUserId->value() === $command->actingUserId->value()) {
            throw new \DomainException('cannot_revoke_permissions_from_self');
        }

        $grant = $this->grants->findByUserAndPermission($command->targetUserId, $command->permissionId);

        if ($grant === null || $grant->status() === \Domain\UserPermissionGrant\GrantStatus::Revoked) {
            return; // Already revoked or doesn't exist
        }

        $grant->revoke();
        
        $this->grants->save($grant);
    }
}
