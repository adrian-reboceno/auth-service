<?php

namespace Application\User\CreateUser;

use Domain\User\User;
use Domain\User\UserId;
use Domain\User\Email;
use Domain\User\Password;
use Domain\User\FullName;
use Domain\User\Locale;
use Domain\User\UserRepositoryInterface;
use Illuminate\Support\Str;

final class CreateUserCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {
    }

    public function handle(CreateUserCommand $command): void
    {
        if ($this->users->findByEmail($command->email) !== null) {
            throw new \DomainException('email_already_registered');
        }

        $user = User::create(
            new UserId((string) Str::uuid()),
            FullName::fromString($command->fullName),
            Email::fromString($command->email),
            Password::fromRaw($command->rawPassword),
            $command->branchId,
            Locale::from($command->locale)
        );

        $this->users->save($user);
    }
}
