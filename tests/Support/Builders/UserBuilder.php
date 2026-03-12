<?php

namespace Tests\Support\Builders;

use Domain\User\User;
use Domain\User\UserId;
use Domain\User\Email;
use Domain\User\Password;
use Domain\User\FullName;
use Domain\User\Locale;
use Illuminate\Support\Str;

final class UserBuilder
{
    private bool $isActive = true;
    private string $passwordHash = '$2y$12$testdummyhashtestdummyhash';
    private array $roles = [];

    private function __construct()
    {
    }

    public static function active(): self
    {
        return new self();
    }

    public function withPasswordHash(string $hash): self
    {
        $this->passwordHash = $hash;
        return $this;
    }

    public function withRoles(array $roleNames): self
    {
        $this->roles = $roleNames;
        return $this;
    }

    public function build(): User
    {
        $user = new User(
            new UserId((string) Str::uuid()),
            FullName::fromString('Test User'),
            Email::fromString('test@pharmacy.com'),
            Password::fromHash($this->passwordHash),
            1, // branch_id
            Locale::from('es'),
            $this->isActive,
            null
        );

        // Not explicitly handling roles via User aggregate because User does not own UserRoleAssignment
        // In actual tests, the DB factory is used to attach roles or we mock the repository.

        return $user;
    }
}
