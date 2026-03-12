<?php

namespace Domain\UserPermissionGrant;

use Domain\User\UserId;
use Domain\Permission\PermissionId;
use Carbon\CarbonImmutable;

final class UserPermissionGrant
{
    private UserPermissionGrantId $id;
    private UserId $userId;
    private PermissionId $permissionId;
    private CarbonImmutable $grantedAt;
    private ?CarbonImmutable $expiresAt;
    private bool $isActive;
    
    private array $domainEvents = [];

    public function __construct(
        UserPermissionGrantId $id,
        UserId $userId,
        PermissionId $permissionId,
        CarbonImmutable $grantedAt,
        ?CarbonImmutable $expiresAt,
        bool $isActive
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->permissionId = $permissionId;
        $this->grantedAt = $grantedAt;
        $this->expiresAt = $expiresAt;
        $this->isActive = $isActive;
    }

    public static function grant(
        UserPermissionGrantId $id,
        UserId $userId,
        PermissionId $permissionId,
        ?CarbonImmutable $expiresAt = null
    ): self {
        $grant = new self(
            $id, 
            $userId, 
            $permissionId, 
            CarbonImmutable::now(), 
            $expiresAt, 
            true
        );
        $grant->recordEvent(new Events\DirectPermissionGranted($userId, $permissionId, $expiresAt));
        return $grant;
    }

    public function revoke(): void
    {
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->recordEvent(new Events\DirectPermissionRevoked($this->userId, $this->permissionId));
    }

    public function markAsExpired(): void
    {
        if ($this->status() === GrantStatus::Expired) {
            // Already naturally expired based on time, but maybe the job wants to disable it permanently
            $this->isActive = false;
            $this->recordEvent(new Events\DirectPermissionExpired($this->userId, $this->permissionId));
        }
    }

    public function status(): GrantStatus
    {
        if (!$this->isActive) {
            return GrantStatus::Revoked;
        }

        if ($this->expiresAt !== null && CarbonImmutable::now()->greaterThanOrEqualTo($this->expiresAt)) {
            return GrantStatus::Expired;
        }

        return GrantStatus::Active;
    }

    public function id(): UserPermissionGrantId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function permissionId(): PermissionId
    {
        return $this->permissionId;
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
