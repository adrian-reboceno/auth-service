<?php

namespace Domain\Token;

final class TokenHash
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromRaw(string $rawToken): self
    {
        return new self(hash('sha256', $rawToken));
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
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
