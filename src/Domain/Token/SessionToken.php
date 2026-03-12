<?php

namespace Domain\Token;

use Domain\User\UserId;
use DateTimeImmutable;

final class SessionToken
{
    private ?SessionTokenId $id;
    private UserId $userId;
    private TokenHash $tokenHash;
    private TokenStatus $status;
    private DateTimeImmutable $expiresAt;

    private array $domainEvents = [];

    public function __construct(
        ?SessionTokenId $id,
        UserId $userId,
        TokenHash $tokenHash,
        TokenStatus $status,
        DateTimeImmutable $expiresAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->tokenHash = $tokenHash;
        $this->status = $status;
        $this->expiresAt = $expiresAt;
    }

    public static function issue(
        UserId $userId,
        TokenHash $tokenHash,
        DateTimeImmutable $expiresAt
    ): self {
        $token = new self(null, $userId, $tokenHash, TokenStatus::Active, $expiresAt);
        $token->recordEvent(new Events\TokenIssued($userId, $tokenHash));
        return $token;
    }

    public function revoke(): void
    {
        if ($this->status !== TokenStatus::Active && $this->status !== TokenStatus::Used) {
            return;
        }

        $this->status = TokenStatus::Revoked;
        $this->recordEvent(new Events\TokenRevoked($this->userId, $this->tokenHash));
    }

    public function markUsed(): void
    {
        if ($this->status !== TokenStatus::Active) {
            return;
        }

        $this->status = TokenStatus::Used;
    }

    public function markExpired(): void
    {
        if ($this->status === TokenStatus::Active && $this->isExpired()) {
            $this->status = TokenStatus::Expired;
        }
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }

    public function setId(SessionTokenId $id): void
    {
        $this->id = $id;
    }

    public function id(): ?SessionTokenId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function tokenHash(): TokenHash
    {
        return $this->tokenHash;
    }

    public function status(): TokenStatus
    {
        return $this->status;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
