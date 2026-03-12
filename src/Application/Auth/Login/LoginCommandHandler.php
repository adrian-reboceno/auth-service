<?php

namespace Application\Auth\Login;

use Domain\Services\UserAuthenticator;
use Application\Shared\PermissionResolver;
use Application\Shared\TokenIssuer;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Domain\Role\RoleRepositoryInterface;
use Domain\User\UserRepositoryInterface;

final class LoginCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserAuthenticator $authenticator,
        private readonly PermissionResolver $permissionResolver,
        private readonly TokenIssuer $tokenIssuer,
        private readonly UserRoleAssignmentRepositoryInterface $roleAssignments,
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, user: array}
     */
    public function handle(LoginCommand $command): array
    {
        // 1. Authenticator (timing safe login check)
        // Rate limiting is handled in the UI/Controller layer (SEC-04).
        // For ADR-08 check, we need to know if the user has any assignments
        // so we retrieve it conceptually, but the authenticator needs to check it.
        // Let's rely on the Authenticator throwing InvalidCredentialsException or we do it here. 
        // We do it before authenticating if possible or we let Authenticator do it.
        // Actually the prompt says "Check >=1 row exists in user_roles" inside UserAuthenticator.
        // However, UserAuthenticator is a Domain Service and shouldn't inject UserRoleAssignmentRepository.
        // "Rule (CRIT-01/02/03): Domain Services operate exclusively on in-memory domain objects... never inject repositories"
        // So the application layer MUST pass boolean `$hasActiveRoles` into `authenticate`.
        
        //$user = $this->authenticator->authenticate($command->email, $command->password, true);
        $user = $this->userRepository->findByEmail($command->email);
        $this->authenticator->authenticate($user, $command->password, true);
        
        // Wait, to know if they have active roles we must query the DB for the user id.
        // But the Authenticator does the dummy hash IF the user is null.
        // $hasActiveRoles requires knowing the user.
        // Workaround to avoid breaking CRIT-01:
        // Authenticate the user first, and if they don't have roles, throw standard error?
        // Let's modify UserAuthenticator slightly so it doesn't need $hasActiveRoles strictly, or we just pass it in after.
        // Actually, PermissionResolver performs the ADR-08 check returning empty array `[]` if no roles.
        // Let's perform authentication, then resolution.

        $permissions = $this->permissionResolver->resolve($user->id(), $user->isActive());
        
        $assignments = $this->roleAssignments->findByUserId($user->id());
        
        if ($assignments->isEmpty()) {
            throw new \DomainException('no_active_roles'); // ADR-08
        }
        
        $roleIds = $assignments->map(fn($a) => $a->roleId())->toArray();
        $roleAggregates = $this->roles->findByIds($roleIds);
        
        $roles = [];
        foreach ($roleAggregates as $role) {
            $roles[] = $role->name()->value();
        }

        // 3. Issue Token
        $tokenData = $this->tokenIssuer->issue($user, $roles, $permissions);

        // Return expected structure
        return [
            'access_token' => $tokenData['access_token'],
            'token_type' => 'Bearer',
            'refresh_token' => $tokenData['refresh_token'],
            'expires_in' => $tokenData['expires_in'],
            'user' => [
                'id' => $user->id()->value(),
                'full_name' => $user->fullName()->value(),
                'email' => $user->email()->value(),
                'roles' => $roles,
                'branch_id' => $user->branchId(),
                'locale' => $user->locale()->value(),
            ]
        ];
    }
}
