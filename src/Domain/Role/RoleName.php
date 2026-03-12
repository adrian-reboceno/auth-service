<?php

namespace Domain\Role;

use InvalidArgumentException;

final class RoleName
{
    private string $value;

    public function __construct(string $value)
    {
        if (mb_strlen($value) > 60) {
            throw new InvalidArgumentException('RoleName cannot exceed 60 characters.');
        }
        
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $value)) {
            throw new InvalidArgumentException('RoleName must be snake_case slug.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
