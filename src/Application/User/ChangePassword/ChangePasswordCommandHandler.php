<?php

namespace Application\User\ChangePassword;

use Domain\User\UserRepositoryInterface;
use Domain\User\Password;
use Application\Shared\SecurityRevoker;
use Infrastructure\Hash\PasswordHasherInterface;

final class ChangePasswordCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly SecurityRevoker $securityRevoker,
        private readonly PasswordHasherInterface $hasher
    ) {
    }

    public function handle(ChangePasswordCommand $command): void
    {
        $user = $this->users->findById($command->userId);

        if ($user === null || !$user->isActive()) {
            throw new \DomainException('user_not_found_or_inactive');
        }

        if (!$this->hasher->verify($command->oldPasswordRaw, $user->password()->hash())) {
            throw new \DomainException('invalid_old_password');
        }

        $newPassword = Password::fromRaw($command->newPasswordRaw);

        // HIGH-02: Password reuse forbidden
        if ($newPassword->isEqualTo($user->password()->hash())) {
            throw new \DomainException('password_reuse_forbidden');
        }

        $user->changePassword($newPassword);
        
        $this->users->save($user);

        // LOW-04: Rotate sessions automatically when changing password
        $this->securityRevoker->revokeAllSessions($user->id());
    }
}
