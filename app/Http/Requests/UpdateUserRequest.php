<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi ubah pengguna. Hanya pengguna yang boleh mengelola akun target (UserPolicy::manage), dan role superadmin hanya bisa diberikan pemegang user.manage-superadmin.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Hanya pengguna yang boleh mengelola akun target.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('user'));
    }

    /**
     * Aturan validasi ubah pengguna.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role' => [
                'required',
                Rule::exists('roles', 'name')->whereNotIn('name', User::nonEditableRolesFor($this->user())),
            ],
        ];
    }

    /**
     * Pesan validasi kustom.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
        ];
    }
}
