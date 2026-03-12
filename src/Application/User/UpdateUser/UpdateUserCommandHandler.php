<?php

namespace Application\User\UpdateUser;

use Domain\User\UserRepositoryInterface;
use Domain\User\FullName;
use Domain\User\Email;
use Domain\User\Locale;

final class UpdateUserCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {
    }

    public function handle(UpdateUserCommand $command): void
    {
        $user = $this->users->findById($command->userId);

        if ($user === null) {
            throw new \DomainException('user_not_found');
        }

        $existingByEmail = $this->users->findByEmail($command->email);
        if ($existingByEmail !== null && $existingByEmail->id()->value() !== $user->id()->value()) {
            throw new \DomainException('email_already_in_use_by_other');
        }

        $user->updateProfile(
            FullName::fromString($command->fullName),
            Email::fromString($command->email)
        );

        if ($user->locale()->value() !== $command->locale) {
            $user->changeLocale(Locale::from($command->locale));
        }

        $this->users->save($user);
    }
}
