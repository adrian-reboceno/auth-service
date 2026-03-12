<?php

namespace Application\Auth\RefreshToken;

final readonly class RefreshTokenCommand
{
    public function __construct(
        public string $refreshTokenRaw,
        public string $ipAddress,
        public string $userAgent
    ) {
    }
}
