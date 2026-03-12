<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

// Infrastructure
use Infrastructure\JWT\JwksBuilder;
use Infrastructure\JWT\RS256TokenGenerator;
use Infrastructure\JWT\TokenGeneratorInterface;
use Infrastructure\Hash\BcryptHasher;
use Infrastructure\Hash\PasswordHasherInterface;

// Repository interfaces
use Domain\User\UserRepositoryInterface;
use Domain\Role\RoleRepositoryInterface;
use Domain\Permission\PermissionRepositoryInterface;
use Domain\Token\TokenRepositoryInterface;
use Domain\Token\ActiveTokenRepositoryInterface;
use Domain\Token\JtiBlacklistRepositoryInterface;
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Domain\UserPermissionGrant\UserPermissionGrantRepositoryInterface;

// Repository implementations
use Infrastructure\Repositories\EloquentUserRepository;
use Infrastructure\Repositories\EloquentRoleRepository;
use Infrastructure\Repositories\EloquentPermissionRepository;
use Infrastructure\Repositories\EloquentTokenRepository;
use Infrastructure\Repositories\EloquentActiveTokenRepository;
use Infrastructure\Repositories\EloquentJtiBlacklistRepository;
use Infrastructure\Repositories\EloquentUserRoleAssignmentRepository;
use Infrastructure\Repositories\EloquentUserPermissionGrantRepository;

// Domain Services
use Domain\Services\PermissionCalculator;
use Domain\Services\TokenPayloadBuilder;
use Domain\Services\UserAuthenticator;

// Application Services
use Application\Shared\PermissionResolver;
use Application\Shared\TokenIssuer;
use Application\Shared\SessionRevoker;
use Application\Shared\SecurityRevoker;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Infrastructure ────────────────────────────────────────────
        $this->app->singleton(JwksBuilder::class,
            fn(Application $app) => new JwksBuilder());

        $this->app->singleton(TokenGeneratorInterface::class,
            fn(Application $app) => new RS256TokenGenerator());

        $this->app->singleton(PasswordHasherInterface::class,
            fn(Application $app) => new BcryptHasher());

        // ── Repositories ──────────────────────────────────────────────
        $this->app->singleton(UserRepositoryInterface::class,
            fn(Application $app) => new EloquentUserRepository());

        $this->app->singleton(RoleRepositoryInterface::class,
            fn(Application $app) => new EloquentRoleRepository());

        $this->app->singleton(PermissionRepositoryInterface::class,
            fn(Application $app) => new EloquentPermissionRepository());

        $this->app->singleton(TokenRepositoryInterface::class,
            fn(Application $app) => new EloquentTokenRepository());

        $this->app->singleton(ActiveTokenRepositoryInterface::class,
            fn(Application $app) => new EloquentActiveTokenRepository());

        $this->app->singleton(JtiBlacklistRepositoryInterface::class,
            fn(Application $app) => new EloquentJtiBlacklistRepository());

        $this->app->singleton(UserRoleAssignmentRepositoryInterface::class,
            fn(Application $app) => new EloquentUserRoleAssignmentRepository());

        $this->app->singleton(UserPermissionGrantRepositoryInterface::class,
            fn(Application $app) => new EloquentUserPermissionGrantRepository());

        // ── Domain Services ───────────────────────────────────────────
        $this->app->singleton(PermissionCalculator::class,
            fn(Application $app) => new PermissionCalculator());

        $this->app->singleton(TokenPayloadBuilder::class,
            fn(Application $app) => new TokenPayloadBuilder());

        $this->app->singleton(UserAuthenticator::class,
            fn(Application $app) => new UserAuthenticator(
                $app->make(PasswordHasherInterface::class)
            ));

        // ── Application Services ──────────────────────────────────────
        $this->app->singleton(PermissionResolver::class,
            fn(Application $app) => new PermissionResolver(
                $app->make(UserRoleAssignmentRepositoryInterface::class),
                $app->make(UserPermissionGrantRepositoryInterface::class),
                $app->make(RoleRepositoryInterface::class),
                $app->make(PermissionCalculator::class)
            ));

        $this->app->singleton(TokenIssuer::class,
            fn(Application $app) => new TokenIssuer(
                $app->make(TokenPayloadBuilder::class),
                $app->make(TokenGeneratorInterface::class),
                $app->make(TokenRepositoryInterface::class),
                $app->make(ActiveTokenRepositoryInterface::class)
            ));

        $this->app->singleton(SessionRevoker::class,
            fn(Application $app) => new SessionRevoker(
                $app->make(TokenRepositoryInterface::class)
            ));

        $this->app->singleton(SecurityRevoker::class,
            fn(Application $app) => new SecurityRevoker(
                $app->make(ActiveTokenRepositoryInterface::class),
                $app->make(JtiBlacklistRepositoryInterface::class),
                $app->make(TokenRepositoryInterface::class)
            ));
    }

    public function boot(): void
    {
        //
    }
}
