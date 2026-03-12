<?php

namespace Application\Auth\Logout;

final readonly class LogoutCommand
{
    public function __construct(
        public string $refreshTokenRaw,
        public string $accessTokenJti // HIGH-06: JTI provided by VerifyJwt middleware
    ) {
    }
}
