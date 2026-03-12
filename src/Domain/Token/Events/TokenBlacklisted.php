<?php

namespace Domain\Token\Events;

use Domain\User\UserId;

final readonly class TokenBlacklisted
{
    public function __construct(
        public string $jti,
        public UserId $userId,
        public string $reason
    ) {
    }
}
