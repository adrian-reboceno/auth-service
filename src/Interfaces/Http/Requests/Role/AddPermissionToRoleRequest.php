<?php
namespace Interfaces\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class AddPermissionToRoleRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return ["permission_id" => "required|uuid"];
    }
    protected function failedValidation(Validator $validator): void {
        throw new HttpResponseException(response()->json(["error" => "validation_failed", "errors" => $validator->errors()], 422));
    }
}