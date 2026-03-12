<?php

namespace Domain\UserRoleAssignment;

use Domain\User\UserId;
use Domain\Role\RoleId;

final class UserRoleAssignment
{
    private UserRoleAssignmentId $id;
    private UserId $userId;
    private RoleId $roleId;
    
    private array $domainEvents = [];

    public function __construct(
        UserRoleAssignmentId $id,
        UserId $userId,
        RoleId $roleId
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->roleId = $roleId;
    }

    public static function assign(
        UserRoleAssignmentId $id,
        UserId $userId,
        RoleId $roleId
    ): self {
        $assignment = new self($id, $userId, $roleId);
        $assignment->recordEvent(new Events\RoleAssignedToUser($userId, $roleId));
        return $assignment;
    }

    public function revoke(): void
    {
        $this->recordEvent(new Events\RoleRevokedFromUser($this->userId, $this->roleId));
    }

    public function id(): UserRoleAssignmentId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function roleId(): RoleId
    {
        return $this->roleId;
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
