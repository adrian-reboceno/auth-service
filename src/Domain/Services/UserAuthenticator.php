<?php

namespace Domain\Services;

use Domain\User\User;
use Domain\Services\Exceptions\InvalidCredentialsException;

final class UserAuthenticator
{
    // SEC-11: pre-generated bcrypt hash for timing safety (cost 12)
    public const DUMMY_HASH = '$2y$12$Z0bWjL9.6rD.gI3I12u9VOFn/N3OQdM5z85T3EFT08w1R3xMMyy3a';

    public function authenticate(?User $user, string $rawPassword, bool $hasActiveRoles = true): User
    {
        if ($user === null) {
            // SEC-11: Execute dummy hash to prevent timing attacks
            password_verify($rawPassword, self::DUMMY_HASH);
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        if (!password_verify($rawPassword, $user->password()->hash())) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        if (!$user->isActive()) {
            throw new InvalidCredentialsException('User is inactive.');
        }

        if (!$hasActiveRoles) {
            throw new InvalidCredentialsException('No active roles assigned.');
        }

        return $user;
    }
}
