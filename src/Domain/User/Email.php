<?php

namespace Domain\User;

use InvalidArgumentException;

final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $email): self
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format.');
        }

        if (strlen($email) > 254) {
            throw new InvalidArgumentException('Email exceeds maximum length of 254 characters (RFC 5322).');
        }

        return new self(strtolower($email));
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
