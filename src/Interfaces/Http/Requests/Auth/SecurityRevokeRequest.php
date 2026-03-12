<?php

namespace Interfaces\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class SecurityRevokeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by checkRole/CheckPermission
    }

    public function rules(): array
    {
        return [];
    }
}
