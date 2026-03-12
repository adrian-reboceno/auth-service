<?php

namespace Infrastructure\Repositories;

use Domain\Token\JtiBlacklistRepositoryInterface;
use Domain\User\UserId;
use App\Models\JtiBlacklist;
use DateTimeImmutable;

final class EloquentJtiBlacklistRepository implements JtiBlacklistRepositoryInterface
{
    public function add(string $jti, UserId $userId, string $reason, DateTimeImmutable $expiresAt): void
    {
        JtiBlacklist::insertOrIgnore([
            'jti' => $jti,
            'user_id' => $userId->value(),
            'reason' => $reason,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);
    }

    public function exists(string $jti): bool
    {
        return JtiBlacklist::where('jti', $jti)->exists();
    }
}
