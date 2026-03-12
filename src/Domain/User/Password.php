<?php

namespace Domain\User;

use Domain\User\Exceptions\InvalidPasswordException;

final class Password
{
    private string $hash;
    private ?string $rawPassword;

    private function __construct(string $hash, ?string $rawPassword = null)
    {
        $this->hash = $hash;
        $this->rawPassword = $rawPassword;
    }

    public static function fromRaw(string $rawPassword): self
    {
        if (strlen($rawPassword) < 8) {
            throw new InvalidPasswordException('Password must be at least 8 characters long.');
        }

        if (!preg_match('/[A-Z]/', $rawPassword)) {
            throw new InvalidPasswordException('Password must contain at least one uppercase letter.');
        }

        if (!preg_match('/[0-9]/', $rawPassword)) {
            throw new InvalidPasswordException('Password must contain at least one number.');
        }

        $hash = password_hash($rawPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        return new self($hash, $rawPassword);
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }

    /**
     * HIGH-02: Prevent password reuse
     */
    public function isEqualTo(string $existingHash): bool
    {
        if ($this->rawPassword === null) {
            // If constructed from hash, we can't reliably check equality with another hash
            return $this->hash === $existingHash;
        }

        return password_verify($this->rawPassword, $existingHash);
    }
}
