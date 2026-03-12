<?php

namespace Domain\Permission;

use InvalidArgumentException;

final class PermissionName
{
    private string $value;
    private string $resource;
    private string $action;

    public function __construct(string $value)
    {
        $parts = explode(':', $value);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('PermissionName must be in format resource:action');
        }

        if (mb_strlen($value) > 80) {
            throw new InvalidArgumentException('PermissionName cannot exceed 80 characters.');
        }

        $this->resource = $parts[0];
        $this->action = $parts[1];
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
