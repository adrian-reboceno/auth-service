<?php

namespace Interfaces\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Domain\Permission\PermissionRepositoryInterface;

final class PermissionController
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions
    ) {
    }

    public function index(): JsonResponse
    {
        $permissions = $this->permissions->findAll();
        
        $data = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id()->value(),
                'name' => $permission->name()->value(),
                'description' => $permission->description(),
            ];
        });

        return response()->json(['data' => $data]);
    }
}
