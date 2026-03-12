<?php

namespace Application\User\UpdateUser;

use Domain\User\UserId;

final readonly class UpdateUserCommand
{
    public function __construct(
        public UserId $userId,
        public string $fullName,
        public string $email,
        public string $locale
    ) {
    }
}
