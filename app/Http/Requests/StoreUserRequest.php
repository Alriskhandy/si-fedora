<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah pengguna. Role superadmin, kaban, dan admin_peran tidak boleh diberikan lewat form ini (User::NON_CREATABLE_ROLES).
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Aturan validasi tambah pengguna.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'role' => [
                'required',
                Rule::exists('roles', 'name')->whereNotIn('name', User::NON_CREATABLE_ROLES),
            ],
            'kabupaten_kota_id' => ['nullable', 'exists:kabupaten_kota,id'],
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
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'kabupaten_kota_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid.',
        ];
    }
}
