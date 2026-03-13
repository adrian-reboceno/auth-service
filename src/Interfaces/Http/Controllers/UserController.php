<?php

namespace Interfaces\Http\Controllers;

use Application\User\CreateUser\CreateUserCommand;
use Application\User\CreateUser\CreateUserCommandHandler;
use Application\User\UpdateUser\UpdateUserCommand;
use Application\User\UpdateUser\UpdateUserCommandHandler;
use Application\User\DeactivateUser\DeactivateUserCommand;
use Application\User\DeactivateUser\DeactivateUserCommandHandler;
use Application\User\ChangePassword\ChangePasswordCommand;
use Application\User\ChangePassword\ChangePasswordCommandHandler;
use Application\Auth\RevokeUserSecurity\RevokeUserSecurityCommandHandler;

use Application\RoleAssignment\AssignRoleToUser\AssignRoleToUserCommand;
use Application\RoleAssignment\AssignRoleToUser\AssignRoleToUserCommandHandler;
use Application\RoleAssignment\RevokeRoleFromUser\RevokeRoleFromUserCommand;
use Application\RoleAssignment\RevokeRoleFromUser\RevokeRoleFromUserCommandHandler;

use Application\PermissionGrant\GrantDirectPermission\GrantDirectPermissionCommand;
use Application\PermissionGrant\GrantDirectPermission\GrantDirectPermissionCommandHandler;
use Application\PermissionGrant\RevokeDirectPermission\RevokeDirectPermissionCommand;
use Application\PermissionGrant\RevokeDirectPermission\RevokeDirectPermissionCommandHandler;

use Interfaces\Http\Requests\User\CreateUserRequest;
use Interfaces\Http\Requests\User\UpdateUserRequest;
use Interfaces\Http\Requests\User\ChangePasswordRequest;
use Interfaces\Http\Requests\User\UpdateLocaleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Domain\User\UserId;
use Domain\Role\RoleId;
use Domain\Permission\PermissionId;
use Carbon\CarbonImmutable;

final class UserController
{
    public function store(
        CreateUserRequest $request,
        CreateUserCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new CreateUserCommand(
                $request->validated('full_name'),
                $request->validated('email'),
                $request->validated('raw_password'),
                $request->validated('branch_id'),
                $request->validated('locale')
            );

            $handler->handle($command);

            return response()->json(['message' => 'user_created'], JsonResponse::HTTP_CREATED);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(
        string $id,
        UpdateUserRequest $request,
        UpdateUserCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new UpdateUserCommand(
                new UserId($id),
                $request->validated('full_name'),
                $request->validated('email'),
                $request->validated('locale')
            );

            $handler->handle($command);

            return response()->json(['message' => 'user_updated']);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function deactivate(
        string $id,
        DeactivateUserCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new DeactivateUserCommand(new UserId($id));
            $handler->handle($command);

            return response()->json(['message' => 'user_deactivated']);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function changePassword(
        ChangePasswordRequest $request,
        ChangePasswordCommandHandler $handler
    ): JsonResponse {
        try {
            // Assuming change password on self
            $userId = $request->attributes->get('user_id');
            if (!$userId) throw new \DomainException('unauthorized');

            $command = new ChangePasswordCommand(
                new UserId($userId),
                $request->validated('old_password'),
                $request->validated('new_password')
            );

            $handler->handle($command);

            return response()->json(['message' => 'password_changed']);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function listPermissions(
        string $id,
        \Application\Shared\PermissionResolver $resolver
    ): JsonResponse {
        try {
            $userId = new UserId($id);
            $user = app(\Domain\User\UserRepositoryInterface::class)->findById($userId);
            if ($user === null) {
                return response()->json(["error" => "user_not_found"], \Illuminate\Http\JsonResponse::HTTP_NOT_FOUND);
            }
            $permissions = $resolver->resolve($userId, $user->isActive());
            return response()->json(["data" => $permissions]);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(["error" => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function assignRole(
        string $id,
        Request $request,
        AssignRoleToUserCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new AssignRoleToUserCommand(
                new UserId($id),
                new RoleId($request->input('role_id')),
                new UserId($request->attributes->get('user_id'))
            );

            $handler->handle($command);
            
            return response()->json(['message' => 'role_assigned']);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function revokeRole(
        string $id,
        string $role_id,
        Request $request,
        RevokeRoleFromUserCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new RevokeRoleFromUserCommand(
                new UserId($id),
                new RoleId($role_id),
                new UserId($request->attributes->get('user_id'))
            );

            $handler->handle($command);

            return response()->json(['message' => 'role_revoked']);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function grantPermission(
        string $id,
        Request $request,
        GrantDirectPermissionCommandHandler $handler
    ): JsonResponse {
        try {
            $expiresAt = $request->input('expires_at') ? new CarbonImmutable($request->input('expires_at')) : null;

            $command = new GrantDirectPermissionCommand(
                new UserId($id),
                new PermissionId($request->input('permission_id')),
                new UserId($request->attributes->get('user_id')),
                $expiresAt
            );

            $handler->handle($command);
            
            return response()->json(['message' => 'permission_granted']);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function revokePermission(
        string $id,
        string $permission_id,
        Request $request,
        RevokeDirectPermissionCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new RevokeDirectPermissionCommand(
                new UserId($id),
                new PermissionId($permission_id),
                new UserId($request->attributes->get('user_id'))
            );

            $handler->handle($command);
            
            return response()->json(['message' => 'permission_revoked']);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function revokeSecurity(
        string $id,
        RevokeUserSecurityCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new RevokeUserSecurityCommand(new UserId($id));
            $handler->handle($command);

            return response()->json(['message' => 'user_security_revoked']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'server_error'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateLocale(
        string $id,
        UpdateLocaleRequest $request,
        UpdateUserCommandHandler $handler // Not the most accurate but simple reuse, or a specific SetLocale command
    ): JsonResponse {
        // Conceptually we need an UpdateLocale Command. We can reuse UpdateUser Command temporarily.
        return response()->json(['message' => 'not_fully_implemented_here'], JsonResponse::HTTP_NOT_IMPLEMENTED);
    }
}
