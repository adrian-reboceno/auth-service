<?php

namespace Application\Shared;

use Domain\Token\TokenRepositoryInterface;
use Domain\Token\TokenHash;

final class SessionRevoker
{
    public function __construct(
        private readonly TokenRepositoryInterface $refreshTokens
    ) {
    }

    public function revoke(string $rawRefreshToken): void
    {
        $hash = TokenHash::fromRaw($rawRefreshToken);
        $token = $this->refreshTokens->findByHash($hash);

        if ($token === null) {
            return; // Nothing to revoke
        }

        $token->revoke();
        
        $this->refreshTokens->save($token);
    }
}
