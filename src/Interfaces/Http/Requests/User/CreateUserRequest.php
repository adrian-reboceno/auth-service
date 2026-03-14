<?php
namespace Interfaces\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class CreateUserRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            "full_name"    => "required|string|max:120",
            "email"        => "required|email|max:254",
            "raw_password" => "required|string|min:8",
            "branch_id"    => "required|integer",
            "locale"       => "required|string|in:es,en",
        ];
    }
    protected function failedValidation(Validator $validator): void {
        throw new HttpResponseException(response()->json(["error" => "validation_failed", "errors" => $validator->errors()], 422));
    }
}