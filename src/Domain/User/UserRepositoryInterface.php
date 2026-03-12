<?php

namespace Domain\User;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function save(User $user): void;

    /**
     * Checks if there's at least one active admin remaining (SEC-02).
     */
    public function countActiveAdmins(): int;
}
