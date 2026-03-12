<?php

namespace Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        // Assuming $this is a Domain Role object
        return [
            'id' => $this->id()->value(),
            'name' => $this->name()->value(),
            'description' => $this->description(),
            'is_active' => $this->isActive(),
            'is_system' => $this->isSystem(),
        ];
    }
}
