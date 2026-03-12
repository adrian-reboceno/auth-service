<?php

namespace Interfaces\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

final class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'uuid'],
        ];
    }
}
