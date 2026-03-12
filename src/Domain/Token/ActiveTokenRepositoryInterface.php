<?php

namespace Domain\Token;

use Domain\User\UserId;

interface ActiveTokenRepositoryInterface
{
    public function add(AccessToken $token): void;

    public function removeByJti(string $jti): void;

    public function removeAllForUser(UserId $userId): void;

    /** @return string[] */
    public function findAllActiveUserIds(): array;

    /**
     * Finds all active JTIs for a user and locks them FOR UPDATE.
     * Returns an array of AccessToken objects.
     * @return AccessToken[]
     */
    public function lockAllForUser(UserId $userId): array;
}
