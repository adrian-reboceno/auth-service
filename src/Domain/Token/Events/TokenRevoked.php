<?php

namespace Domain\Token\Events;

use Domain\User\UserId;
use Domain\Token\TokenHash;

final readonly class TokenRevoked
{
    public function __construct(
        public UserId $userId,
        public TokenHash $tokenHash
    ) {
    }
}
