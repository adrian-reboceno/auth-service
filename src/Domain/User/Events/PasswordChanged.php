<?php

namespace Domain\User\Events;

use Domain\User\UserId;

final readonly class PasswordChanged
{
    public function __construct(public UserId $userId)
    {
    }
}
