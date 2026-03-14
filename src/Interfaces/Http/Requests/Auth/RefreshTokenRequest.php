<?php
namespace Interfaces\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RefreshTokenRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return ["refresh_token" => "required|string"];
    }
    protected function failedValidation(Validator $validator): void {
        throw new HttpResponseException(response()->json(["error" => "validation_failed", "errors" => $validator->errors()], 422));
    }
}