<?php

namespace Domain\User;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;
    
    /** @return \Illuminate\Support\Collection */
    public function findAll(): \Illuminate\Support\Collection;

    public function findByEmail(string $email): ?User;
    
    public function save(User $user): void;

    /**
     * Checks if there's at least one active admin remaining (SEC-02).
     */
    public function countActiveAdmins(): int;
}
