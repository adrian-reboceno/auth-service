<?php

namespace Application\User\CreateUser;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $fullName,
        public string $email,
        public string $rawPassword,
        public int $branchId,
        public string $locale
    ) {
    }
}
