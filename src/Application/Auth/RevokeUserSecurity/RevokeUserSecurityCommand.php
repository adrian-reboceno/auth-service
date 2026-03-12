<?php

namespace Application\Auth\RevokeUserSecurity;

use Domain\User\UserId;

final readonly class RevokeUserSecurityCommand
{
    public function __construct(public UserId $userId)
    {
    }
}
