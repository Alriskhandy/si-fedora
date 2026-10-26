<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Validasi tambah role. Hanya pemegang role.manage-protected (RolePolicy::create).
 */
class StoreRoleRequest extends FormRequest
{
    /**
     * Hanya pengguna yang boleh menambah role.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Role::class);
    }

    /**
     * Aturan validasi tambah role.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(array_keys(config('permissions.permissions')))],
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
            'name.required' => 'Nama role wajib diisi.',
            'name.regex' => 'Nama role hanya boleh huruf kecil, angka, dan garis bawah.',
            'name.unique' => 'Nama role sudah dipakai.',
            'permissions.*.in' => 'Hak akses yang dipilih tidak valid.',
        ];
    }
}
