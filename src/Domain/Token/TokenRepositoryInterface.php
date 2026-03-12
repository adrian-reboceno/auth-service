<?php

namespace Domain\Token;

use Domain\User\UserId;

interface TokenRepositoryInterface
{
    public function findByHash(TokenHash $hash): ?SessionToken;

    public function save(SessionToken $token): void;
    
    /**
     * Revokes all active/used refresh tokens for a user.
     */
    public function revokeAllForUser(UserId $userId): void;
}
