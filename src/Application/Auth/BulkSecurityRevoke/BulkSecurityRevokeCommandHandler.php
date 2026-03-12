<?php

namespace Application\Auth\BulkSecurityRevoke;

use Application\Shared\SecurityRevoker;
use Domain\Token\ActiveTokenRepositoryInterface;
use Domain\User\UserId;

final class BulkSecurityRevokeCommandHandler
{
    public function __construct(
        private readonly SecurityRevoker $securityRevoker,
        private readonly ActiveTokenRepositoryInterface $activeTokens
    ) {}

    public function handle(BulkSecurityRevokeCommand $command): array
    {
        $userIds = $this->activeTokens->findAllActiveUserIds();
        foreach ($userIds as $userId) {
            $this->securityRevoker->revokeAllSessions(new UserId($userId));
        }
        return ["users_revoked" => count($userIds)];
    }
}
