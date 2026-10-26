<?php

namespace App\Http\Requests;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah pengguna. Role terlindungi (superadmin, kaban, admin_peran) hanya bisa diberikan Superadmin (User::nonAssignableRolesFor).
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
                Rule::exists('roles', 'name')->whereNotIn('name', User::nonAssignableRolesFor($this->user())),
            ],
            'kabupaten_kota_id' => ['nullable', 'exists:kabupaten_kota,id'],
            'opd_id' => [
                'nullable',
                Rule::requiredIf($this->input('role') === 'opd'),
                Rule::exists('opd', 'id')->where('status', Opd::STATUS_AKTIF),
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
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'kabupaten_kota_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid.',
            'opd_id.required' => 'OPD wajib dipilih untuk akun OPD.',
            'opd_id.exists' => 'OPD yang dipilih tidak valid atau nonaktif.',
        ];
    }
}
