<?php

namespace Interfaces\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

final class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/'],
            'description' => ['nullable', 'string', 'max:255'], // Should strip HTML in controller or here if possible
        ];
    }
    
    protected function prepareForValidation()
    {
        if ($this->has('description')) {
            $this->merge([
                'description' => strip_tags($this->description)
            ]);
        }
    }
}
