<?php

namespace Interfaces\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'locale' => 'required|string|in:es,en',
        ];
    }
}
