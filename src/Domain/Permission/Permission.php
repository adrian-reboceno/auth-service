<?php

namespace Domain\Permission;

final class Permission
{
    private PermissionId $id;
    private PermissionName $name;
    private ?string $description;

    private array $domainEvents = [];

    public function __construct(
        PermissionId $id,
        PermissionName $name,
        ?string $description
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public static function create(
        PermissionId $id,
        PermissionName $name,
        ?string $description
    ): self {
        $permission = new self($id, $name, $description);
        $permission->recordEvent(new Events\PermissionCreated($id, $name));
        return $permission;
    }

    public function id(): PermissionId
    {
        return $this->id;
    }

    public function name(): PermissionName
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }


    public function updateDescription(?string $description): void
    {
        $this->description = $description;
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
