<?php

namespace Interfaces\Http\Controllers;

use Application\Permission\CreatePermission\CreatePermissionCommand;
use Application\Permission\CreatePermission\CreatePermissionCommandHandler;
use Application\Permission\UpdatePermission\UpdatePermissionCommand;
use Application\Permission\UpdatePermission\UpdatePermissionCommandHandler;
use Domain\Permission\PermissionId;
use Domain\Permission\PermissionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PermissionController
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions
    ) {}

    public function index(): JsonResponse
    {
        $data = $this->permissions->findAll()->map(fn($p) => [
            "id"          => $p->id()->value(),
            "name"        => $p->name()->value(),
            "description" => $p->description(),
        ]);
        return response()->json(["data" => $data]);
    }

    public function show(string $id): JsonResponse
    {
        $permission = $this->permissions->findById(new PermissionId($id));
        if ($permission === null) {
            return response()->json(["error" => "permission_not_found"], JsonResponse::HTTP_NOT_FOUND);
        }
        return response()->json([
            "id"          => $permission->id()->value(),
            "name"        => $permission->name()->value(),
            "description" => $permission->description(),
        ]);
    }

    public function store(
        Request $request,
        CreatePermissionCommandHandler $handler
    ): JsonResponse {
        try {
            $name = $request->input("name");
            $description = $request->input("description");
            if (empty($name)) {
                return response()->json(["error" => "name_required"], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            $id = $handler->handle(new CreatePermissionCommand($name, $description));
            return response()->json(["message" => "permission_created", "id" => $id], JsonResponse::HTTP_CREATED);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json(["error" => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(
        string $id,
        Request $request,
        UpdatePermissionCommandHandler $handler
    ): JsonResponse {
        try {
            $handler->handle(new UpdatePermissionCommand(
                new PermissionId($id),
                $request->input("description")
            ));
            return response()->json(["message" => "permission_updated"]);
        } catch (\DomainException $e) {
            return response()->json(["error" => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
