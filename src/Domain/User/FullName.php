<?php

namespace Domain\User;

use InvalidArgumentException;

final class FullName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $name): self
    {
        $cleaned = trim(strip_tags($name));
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned);

        if ($cleaned === '') {
            throw new InvalidArgumentException('FullName cannot be empty.');
        }

        if (mb_strlen($cleaned) > 120) {
            throw new InvalidArgumentException('FullName cannot exceed 120 characters.');
        }

        return new self($cleaned);
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
