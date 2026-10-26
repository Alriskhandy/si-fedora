<?php

namespace App\Http\Requests;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi ubah pengguna. Hanya pengguna yang boleh mengelola akun target (UserPolicy::manage).
 * Role terlindungi (superadmin, kaban, admin_peran) hanya bisa diberikan atau diubah Superadmin:
 * Admin tidak bisa memberi role tersebut, dan role akun yang sudah memilikinya tidak bisa diubah.
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
            'role' => $this->route('user')->isRoleLockedFor($this->user())
                ? ['required', Rule::in($this->route('user')->getRoleNames()->all())]
                : ['required', Rule::exists('roles', 'name')->whereNotIn('name', User::nonAssignableRolesFor($this->user()))],
            'opd_id' => [
                'nullable',
                Rule::requiredIf($this->input('role') === 'opd'),
                // OPD yang sudah terhubung tetap sah walau kini nonaktif
                Rule::exists('opd', 'id')->where(fn ($query) => $query->where('status', Opd::STATUS_AKTIF)->orWhere('id', $this->route('user')->opd_id)),
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
            'role.in' => 'Role akun ini hanya dapat diubah Superadmin.',
            'opd_id.required' => 'OPD wajib dipilih untuk akun OPD.',
            'opd_id.exists' => 'OPD yang dipilih tidak valid atau nonaktif.',
        ];
    }
}
