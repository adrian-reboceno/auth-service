<?php
namespace Interfaces\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class ChangePasswordRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            "old_password" => "required|string",
            "new_password" => "required|string|min:8",
        ];
    }
    protected function failedValidation(Validator $validator): void {
        throw new HttpResponseException(response()->json(["error" => "validation_failed", "errors" => $validator->errors()], 422));
    }
}