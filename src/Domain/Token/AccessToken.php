<?php

namespace Domain\Token;

use Domain\User\UserId;
use DateTimeImmutable;

final class AccessToken
{
    private string $jti;
    private UserId $userId;
    private DateTimeImmutable $expiresAt;

    public function __construct(
        string $jti,
        UserId $userId,
        DateTimeImmutable $expiresAt
    ) {
        $this->jti = $jti;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
    }

    public function jti(): string
    {
        return $this->jti;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }
}
