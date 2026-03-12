<?php

namespace Interfaces\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Domain\Role\RoleRepositoryInterface;
use Domain\Role\RoleId;
use Domain\Permission\PermissionId;

use Application\Role\CreateRole\CreateRoleCommand;
use Application\Role\CreateRole\CreateRoleCommandHandler;
use Application\Role\UpdateRole\UpdateRoleCommand;
use Application\Role\UpdateRole\UpdateRoleCommandHandler;
use Application\Role\DeactivateRole\DeactivateRoleCommand;
use Application\Role\DeactivateRole\DeactivateRoleCommandHandler;
use Application\Role\AddPermissionToRole\AddPermissionToRoleCommand;
use Application\Role\AddPermissionToRole\AddPermissionToRoleCommandHandler;
use Application\Role\RemovePermissionFromRole\RemovePermissionFromRoleCommand;
use Application\Role\RemovePermissionFromRole\RemovePermissionFromRoleCommandHandler;

use Interfaces\Http\Requests\Role\CreateRoleRequest;
use Interfaces\Http\Requests\Role\UpdateRoleRequest;
use Interfaces\Http\Requests\Role\AddPermissionToRoleRequest;

final class RoleController
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles
    ) {
    }

    public function index(): JsonResponse
    {
        $roles = $this->roles->findAll();
        
        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id()->value(),
                'name' => $role->name()->value(),
                'is_active' => $role->isActive(),
                'is_system' => $role->isSystem(),
                'permissions' => array_map(fn($p) => $p->value(), $role->permissionIds()),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(CreateRoleRequest $request, CreateRoleCommandHandler $handler): JsonResponse
    {
        try {
            $command = new CreateRoleCommand(
                $request->validated('name'),
                $request->validated('description')
            );
            $handler->handle($command);
            return response()->json(['message' => 'role_created'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdateRoleRequest $request, UpdateRoleCommandHandler $handler): JsonResponse
    {
        try {
            $command = new UpdateRoleCommand(
                new RoleId($id),
                $request->validated('name'),
                $request->validated('description')
            );
            $handler->handle($command);
            return response()->json(['message' => 'role_updated']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function deactivate(string $id, DeactivateRoleCommandHandler $handler): JsonResponse
    {
        try {
            $command = new DeactivateRoleCommand(new RoleId($id));
            $handler->handle($command);
            return response()->json(['message' => 'role_deactivated']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function addPermission(string $id, AddPermissionToRoleRequest $request, AddPermissionToRoleCommandHandler $handler): JsonResponse
    {
        try {
            $command = new AddPermissionToRoleCommand(
                new RoleId($id),
                new PermissionId($request->validated('permission_id'))
            );
            $handler->handle($command);
            return response()->json(['message' => 'permission_added']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function removePermission(string $id, string $permission_id, RemovePermissionFromRoleCommandHandler $handler): JsonResponse
    {
        try {
            $command = new RemovePermissionFromRoleCommand(
                new RoleId($id),
                new PermissionId($permission_id)
            );
            $handler->handle($command);
            return response()->json(['message' => 'permission_removed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
