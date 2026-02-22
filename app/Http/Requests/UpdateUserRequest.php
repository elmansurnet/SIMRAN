<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class UpdateUserRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->isSuperadmin(); }
    public function rules(): array {
        $userId = $this->route('user')?->id;
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role'     => ['required', Rule::in(['pic', 'auditor'])],
        ];
    }
    public function messages(): array {
        return ['email.unique' => 'Email sudah digunakan oleh pengguna lain.'];
    }
}
