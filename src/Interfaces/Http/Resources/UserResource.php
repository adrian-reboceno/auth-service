<?php

namespace Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id()->value(),
            'full_name' => $this->fullName()->value(),
            'email' => $this->email()->value(),
            'branch_id' => $this->branchId(),
            'locale' => $this->locale()->value(),
            'is_active' => $this->isActive(),
            'created_at' => $this->createdAt(),
        ];
    }
}
