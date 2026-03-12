<?php

namespace Application\User\DeactivateUser;

use Domain\User\UserId;

final readonly class DeactivateUserCommand
{
    public function __construct(public UserId $userId)
    {
    }
}
