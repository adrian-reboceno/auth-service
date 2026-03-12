<?php

namespace Application\Auth\RefreshToken;

use Domain\Token\TokenRepositoryInterface;
use Domain\Token\TokenHash;
use Domain\Token\TokenStatus;
use Domain\User\UserRepositoryInterface;
use Application\Shared\PermissionResolver;
use Application\Shared\TokenIssuer;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Domain\Token\ActiveTokenRepositoryInterface;
use Domain\Role\RoleRepositoryInterface;

final class RefreshTokenCommandHandler
{
    public function __construct(
        private readonly TokenRepositoryInterface $refreshTokens,
        private readonly UserRepositoryInterface $users,
        private readonly PermissionResolver $permissionResolver,
        private readonly TokenIssuer $tokenIssuer,
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments,
        private readonly ActiveTokenRepositoryInterface $activeTokens,
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function handle(RefreshTokenCommand $command): array
    {
        $hash = TokenHash::fromRaw($command->refreshTokenRaw);
        $token = $this->refreshTokens->findByHash($hash);

        if ($token === null) {
            throw new \DomainException('token_invalid');
        }

        // SEC-05: Reuse detection
        if ($token->status() === TokenStatus::Used) {
            $this->refreshTokens->revokeAllForUser($token->userId());
            $this->activeTokens->removeAllForUser($token->userId());
            throw new \DomainException('token_reuse_detected');
        }

        if ($token->status() !== TokenStatus::Active || $token->isExpired()) {
            throw new \DomainException('token_expired');
        }

        $user = $this->users->findById($token->userId());
        if ($user === null || !$user->isActive()) {
            throw new \DomainException('user_inactive');
        }

        // Rotate token
        $token->markUsed();
        $this->refreshTokens->save($token);

        // ADR-03: Re-resolve permissions against current DB state
        $permissions = $this->permissionResolver->resolve($user->id(), $user->isActive());

        $assignments = $this->roleAssignments->findByUserId($user->id());
        if ($assignments->isEmpty()) {
            throw new \DomainException('no_active_roles'); // ADR-08
        }

        $roleIds = $assignments->map(fn($a) => $a->roleId())->toArray();
        $roleAggregates = $this->roles->findByIds($roleIds);

        $roles = [];
        foreach ($roleAggregates as $role) {
            $roles[] = $role->name()->value();
        }

        // Issue new token pair
        $tokenData = $this->tokenIssuer->issue($user, $roles, $permissions);

        return [
            'access_token' => $tokenData['access_token'],
            'token_type' => 'Bearer',
            'refresh_token' => $tokenData['refresh_token'],
            'expires_in' => $tokenData['expires_in'],
        ];
    }
}
