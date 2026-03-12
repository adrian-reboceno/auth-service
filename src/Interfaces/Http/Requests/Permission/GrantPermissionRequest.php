<?php

namespace Interfaces\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

final class GrantPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_id' => ['required', 'uuid'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
