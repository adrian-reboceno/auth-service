<?php

namespace Domain\Services;

use Domain\User\User;

final class TokenPayloadBuilder
{
    /**
     * @param string[] $roles (e.g., ['cashier', 'warehouse'])
     * @param string[] $permissions (e.g., ['inventory:read', 'sales:create'])
     */
    public function build(
        User $user,
        string $jti,
        int $expiresAt,
        array $roles,
        array $permissions
    ): array {
        return [
            'iss' => 'pharmacy-auth-service',
            'sub' => $user->id()->value(),
            'iat' => time(),
            'exp' => $expiresAt,
            'jti' => $jti,
            'name' => $user->fullName()->value(),
            'email' => $user->email()->value(),
            'roles' => $roles,
            'branch_id' => $user->branchId(),
            'locale' => $user->locale()->value(),
            'permissions' => $permissions,
        ];
    }
}
