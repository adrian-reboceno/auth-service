<?php

namespace Domain\Role;

use Domain\Permission\PermissionId;
use Domain\Permission\PermissionName;

final class Role
{
    private RoleId $id;
    private RoleName $name;
    /** @var PermissionName[] */
    private array $permissionNames;
    private bool $isActive;
    private bool $isSystem;
    private ?string $description = null;
    private array $domainEvents = [];

    /**
     * @param PermissionName[] $permissionNames
     */
    public function __construct(
        RoleId $id,
        RoleName $name,
        array $permissionNames,
        bool $isActive,
        bool $isSystem
    ) {
        $this->id             = $id;
        $this->name           = $name;
        $this->permissionNames = array_values($permissionNames);
        $this->isActive       = $isActive;
        $this->isSystem       = $isSystem;
    }

    public static function create(RoleId $id, RoleName $name, ?string $description = null, bool $isSystem = false): self
    {
        $role = new self($id, $name, [], true, $isSystem);
        $role->description = $description;
        $role->recordEvent(new Events\RoleCreated($id, $name));
        return $role;
    }


    public function rename(RoleName $name): void
    {
        $this->name = $name;
    }

    public function withDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function updateDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function deactivate(): void
    {
        if ($this->isSystem) {
            throw new \DomainException('System roles cannot be deactivated.');
        }
        if (!$this->isActive) return;
        $this->isActive = false;
        $this->recordEvent(new Events\RoleDeactivated($this->id));
    }

    public function addPermissionByName(PermissionName $name): void
    {
        foreach ($this->permissionNames as $existing) {
            if ($existing->value() === $name->value()) return;
        }
        $this->permissionNames[] = $name;
    }

    public function removePermissionByName(PermissionName $name): void
    {
        $filtered = array_filter(
            $this->permissionNames,
            fn(PermissionName $n) => $n->value() !== $name->value()
        );
        $this->permissionNames = array_values($filtered);
    }

    /** @return string[] — permission names e.g. ['users:create', 'roles:assign'] */
    public function activePermissionNames(): array
    {
        if (!$this->isActive) return [];
        return array_map(fn(PermissionName $n) => $n->value(), $this->permissionNames);
    }

    /** @return PermissionName[] */
    public function permissionNames(): array
    {
        return $this->permissionNames;
    }

    public function id(): RoleId       { return $this->id; }
    public function name(): RoleName   { return $this->name; }
    public function isActive(): bool   { return $this->isActive; }
    public function isSystem(): bool   { return $this->isSystem; }

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
