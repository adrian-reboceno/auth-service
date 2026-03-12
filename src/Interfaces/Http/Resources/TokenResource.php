<?php

namespace Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'access_token' => $this['access_token'],
            'token_type' => $this['token_type'] ?? 'Bearer',
            'expires_in' => $this['expires_in'],
            'refresh_token' => $this['refresh_token'] ?? null,
            'user' => $this['user'] ?? null,
        ];
    }
}
