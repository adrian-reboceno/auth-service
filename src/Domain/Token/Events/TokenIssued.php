<?php

namespace Domain\Token\Events;

use Domain\User\UserId;
use Domain\Token\TokenHash;

final readonly class TokenIssued
{
    public function __construct(
        public UserId $userId,
        public TokenHash $tokenHash
    ) {
    }
}
