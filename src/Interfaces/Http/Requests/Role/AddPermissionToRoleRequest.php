<?php

namespace Interfaces\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

final class AddPermissionToRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_id' => ['required', 'uuid'],
        ];
    }
}
