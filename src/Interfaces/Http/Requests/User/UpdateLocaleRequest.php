<?php

namespace Interfaces\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'in:es,en'],
        ];
    }
}
