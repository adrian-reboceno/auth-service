<?php

use Illuminate\Support\Facades\Route;
use Interfaces\Http\Controllers\AuthController;
use Interfaces\Http\Controllers\UserController;
use Interfaces\Http\Controllers\RoleController;
use Interfaces\Http\Controllers\PermissionController;
use Interfaces\Http\Middleware\RateLimitAuth;
use Interfaces\Http\Middleware\VerifyJwt;
use Interfaces\Http\Middleware\CheckRole;
use Interfaces\Http\Middleware\CheckPermission;
use Interfaces\Http\Middleware\SetLocale;

Route::prefix('v1')->middleware(['api', SetLocale::class])->group(function () {

    // ── Public — Auth ────────────────────────────────────────────────
    Route::post('/auth/login',   [AuthController::class, 'login'])
        ->middleware(RateLimitAuth::class);

    Route::post('/auth/refresh', [AuthController::class, 'refresh'])
        ->middleware(RateLimitAuth::class);

    Route::get('/auth/jwks',     [AuthController::class, 'jwks']);   // SEC-09 — no auth required

    // ── Authenticated ─────────────────────────────────────────────────
    Route::middleware(VerifyJwt::class)->group(function () {

        // Auth
        Route::post('/auth/logout',           [AuthController::class, 'logout']);
        Route::post('/auth/security-revoke',  [AuthController::class, 'securityRevoke']);
        Route::get('/auth/me',                [AuthController::class, 'me']);

        // Own profile
        Route::put('/users/{id}/password', [UserController::class, 'changePassword']);
        Route::put('/users/{id}/locale',   [UserController::class, 'updateLocale']);

        // ── Requires users:manage (SEC-06) ───────────────────────────
        Route::middleware(CheckRole::class . ':super_admin')->group(function () {

            // User management
            Route::get('/users',            [UserController::class, 'index']);
            Route::post('/users',           [UserController::class, 'store']);
            Route::get('/users/{id}',       [UserController::class, 'show']);
            Route::put('/users/{id}',       [UserController::class, 'update']);
            Route::delete('/users/{id}',    [UserController::class, 'deactivate']);

            // Role assignments — SEC-01 self-modification blocked in handler
            Route::get('/users/{id}/roles',                          [UserController::class, 'listRoles']);
            Route::post('/users/{id}/roles',                         [UserController::class, 'assignRole']);
            Route::delete('/users/{id}/roles/{role_id}',             [UserController::class, 'revokeRole']);

            // Direct permission grants — SEC-01 self-modification blocked in handler
            Route::get('/users/{id}/permissions',                    [UserController::class, 'listPermissions']);
            Route::post('/users/{id}/permissions',                   [UserController::class, 'grantPermission']);
            Route::delete('/users/{id}/permissions/{permission_id}', [UserController::class, 'revokePermission']);

            // Security revocation — CRIT-05 atomic transaction
            Route::post('/auth/users/{id}/security-revoke',          [AuthController::class, 'securityRevokeUser']);
            Route::post('/auth/security-revoke-all',                 [AuthController::class, 'bulkSecurityRevoke']); // LOW-05

            // Role management
            Route::get('/roles',                                     [RoleController::class, 'index']);
            Route::post('/roles',                                    [RoleController::class, 'store']);
            Route::get('/roles/{id}',                                [RoleController::class, 'show']);
            Route::put('/roles/{id}',                                [RoleController::class, 'update']);
            Route::delete('/roles/{id}',                             [RoleController::class, 'deactivate']);

            // Role ↔ Permission assignments
            Route::get('/roles/{id}/permissions',                    [RoleController::class, 'listPermissions']);
            Route::post('/roles/{id}/permissions',                   [RoleController::class, 'addPermission']);
            Route::delete('/roles/{id}/permissions/{permission_id}', [RoleController::class, 'removePermission']);

            // Permission registry — SEC-06: requires users:manage
            Route::get('/permissions', [PermissionController::class, 'index']);
            Route::post('/permissions', [PermissionController::class, 'store']);
            Route::get('/permissions/{id}', [PermissionController::class, 'show']);
            Route::put('/permissions/{id}', [PermissionController::class, 'update']);
        });
    });
});