<?php

namespace Application\User\ChangePassword;

use Domain\User\UserId;

final readonly class ChangePasswordCommand
{
    public function __construct(
        public UserId $userId,
        public string $oldPasswordRaw,
        public string $newPasswordRaw
    ) {
    }
}
