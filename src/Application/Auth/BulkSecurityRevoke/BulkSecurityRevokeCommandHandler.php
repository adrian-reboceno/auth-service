<?php

namespace Application\Auth\BulkSecurityRevoke;

use Application\Shared\SecurityRevoker;
use Domain\User\UserRepositoryInterface;

final class BulkSecurityRevokeCommandHandler
{
    public function __construct(
        private readonly SecurityRevoker $securityRevoker,
        private readonly UserRepositoryInterface $users
    ) {
    }

    /**
     * @return array{users_revoked: int}
     */
    public function handle(BulkSecurityRevokeCommand $command): array
    {
        // Conceptual: The actual implementation would iterate over active sessions or all users.
        // Given UserRepositoryInterface doesn't have findAll yet, 
        // we could just execute this directly via DB in Infrastructure,
        // or add a method `findAllActive()` to the user repository.
        // For simplicity and to stick to Domain logic, we assume we fetch users.
        // Let's assume we invoke a method on the ActiveTokens repository to get unique UserIds.
        throw new \RuntimeException('Not fully implemented yet.');
    }
}
