<?php
namespace Interfaces\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateUserRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            "full_name" => "sometimes|required|string|max:120",
            "email"     => "sometimes|required|email|max:254",
            "locale"    => "sometimes|required|string|in:es,en",
        ];
    }
    protected function failedValidation(Validator $validator): void {
        throw new HttpResponseException(response()->json(["error" => "validation_failed", "errors" => $validator->errors()], 422));
    }
}