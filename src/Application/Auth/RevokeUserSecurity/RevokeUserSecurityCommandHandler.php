<?php

namespace Application\Auth\RevokeUserSecurity;

use Application\Shared\SecurityRevoker;

final class RevokeUserSecurityCommandHandler
{
    public function __construct(
        private readonly SecurityRevoker $securityRevoker
    ) {
    }

    public function handle(RevokeUserSecurityCommand $command): void
    {
        $this->securityRevoker->revokeAllSessions($command->userId);
    }
}
