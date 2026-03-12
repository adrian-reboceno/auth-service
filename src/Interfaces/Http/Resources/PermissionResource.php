<?php

namespace Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray($request): array
    {
        // Assuming $this is a Domain Permission object
        return [
            'id' => $this->id()->value(),
            'name' => $this->name()->value(),
            'resource' => $this->resource(),
            'action' => $this->action(),
            'description' => $this->description(),
        ];
    }
}
