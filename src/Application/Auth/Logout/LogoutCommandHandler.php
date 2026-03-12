<?php

namespace Application\Auth\Logout;

use Application\Shared\SessionRevoker;
use Domain\Token\ActiveTokenRepositoryInterface;

final class LogoutCommandHandler
{
    public function __construct(
        private readonly SessionRevoker $sessionRevoker,
        private readonly ActiveTokenRepositoryInterface $activeTokens
    ) {
    }

    public function handle(LogoutCommand $command): void
    {
        // 1. Revoke the refresh token
        $this->sessionRevoker->revoke($command->refreshTokenRaw);

        // 2. Delete the active access token JTI (HIGH-06)
        $this->activeTokens->removeByJti($command->accessTokenJti);
    }
}
