<?php

namespace Interfaces\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:120',
            'email' => 'required|email|max:254',
            'raw_password' => 'required|string|min:8', // Domain enforces complexity
            'branch_id' => 'required|integer',
            'locale' => 'required|string|in:es,en',
        ];
    }
}
