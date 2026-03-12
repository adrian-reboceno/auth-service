<?php

namespace Domain\Token;

use Domain\User\UserId;
use DateTimeImmutable;

interface JtiBlacklistRepositoryInterface
{
    public function add(string $jti, UserId $userId, string $reason, DateTimeImmutable $expiresAt): void;

    public function exists(string $jti): bool;
}
