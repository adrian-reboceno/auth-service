<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// 1. Repositorios
use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Infrastructure\Repositories\EloquentUserRoleAssignmentRepository;

use Domain\UserPermissionGrant\UserPermissionGrantRepositoryInterface;
use Infrastructure\Repositories\EloquentUserPermissionGrantRepository;

use Domain\Role\RoleRepositoryInterface;
use Infrastructure\Repositories\EloquentRoleRepository;

// 2. NUEVO: Motor de Autenticación (JWT)
use Infrastructure\JWT\TokenGeneratorInterface;
use Infrastructure\JWT\RS256TokenGenerator;

use Domain\Token\TokenRepositoryInterface;
use Infrastructure\Repositories\EloquentTokenRepository;

use Domain\Token\ActiveTokenRepositoryInterface;
use Infrastructure\Repositories\EloquentActiveTokenRepository;

use Domain\User\UserRepositoryInterface;
use Infrastructure\Repositories\EloquentUserRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registro de Roles y Asignaciones
        $this->app->bind(
            UserRoleAssignmentRepositoryInterface::class,
            EloquentUserRoleAssignmentRepository::class
        );

        $this->app->bind(
            RoleRepositoryInterface::class,
            EloquentRoleRepository::class
        );

        // Registro de Permisos
        $this->app->bind(
            UserPermissionGrantRepositoryInterface::class,
            EloquentUserPermissionGrantRepository::class
        );

        // 3. NUEVO: Registro del Generador de Tokens
        // Esto resolverá el error de "TokenGeneratorInterface is not instantiable"
        $this->app->bind(
            TokenGeneratorInterface::class,
            RS256TokenGenerator::class
        );

        $this->app->bind(
            TokenRepositoryInterface::class,
            EloquentTokenRepository::class
        );

        $this->app->bind(
            ActiveTokenRepositoryInterface::class,
            EloquentActiveTokenRepository::class
        );
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(TokenGeneratorInterface::class, function ($app) {
            return new RS256TokenGenerator(
                // Aquí le pasamos la ruta definida en tu config o .env
                config('auth.jwt.private_key_path', '/var/www/tests/keys/private.pem'),
                config('auth.jwt.public_key_path', '/var/www/tests/keys/public.pem')
            );
        });
    }

    public function boot(): void
    {
        //
    }
}