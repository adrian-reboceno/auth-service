<?php

namespace Application\Shared;

use Domain\User\UserId;
use Domain\Token\ActiveTokenRepositoryInterface;
use Domain\Token\JtiBlacklistRepositoryInterface;
use Domain\Token\TokenRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Domain\Token\Events\TokenBlacklisted;

final class SecurityRevoker
{
    public function __construct(
        private readonly ActiveTokenRepositoryInterface $activeTokens,
        private readonly JtiBlacklistRepositoryInterface $jtiBlacklist,
        private readonly TokenRepositoryInterface $refreshTokens
    ) {
    }

    /**
     * CRIT-05: Atomic full-session revocation inside a transaction.
     * Prevents race conditions escaping blacklist.
     */
    public function revokeAllSessions(UserId $userId): void
    {
        DB::transaction(function () use ($userId) {
            // Lock rows to prevent concurrent INSERT escaping the blacklist
            $activeJtis = $this->activeTokens->lockAllForUser($userId);

            foreach ($activeJtis as $token) {
                // Insert into blacklist directly
                $this->jtiBlacklist->add(
                    $token->jti(),
                    $userId,
                    'security_revocation',
                    $token->expiresAt()
                );
            }

            // Remove all JTIs for this user from active_tokens
            $this->activeTokens->removeAllForUser($userId);

            // Revoke all refresh tokens
            $this->refreshTokens->revokeAllForUser($userId);
        });
    }
}
