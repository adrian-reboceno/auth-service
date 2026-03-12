<?php

namespace Domain\User;

use InvalidArgumentException;

final class UserId
{
    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            throw new InvalidArgumentException('UserId must be a valid UUID v4.');
        }

        $this->value = strtolower($value);
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
