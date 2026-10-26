<?php

namespace App\Http\Requests;

use App\Policies\RolePolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi ubah hak akses role. Pengguna harus boleh mengubah role ini (RolePolicy::update)
 * dan tidak mengirim permission yang terkunci baginya (RolePolicy::lockedPermissionsFor).
 */
class UpdateRoleRequest extends FormRequest
{
    /**
     * Tolak (403) bila role terlindungi atau permission terkunci ikut dikirim.
     */
    public function authorize(): bool
    {
        $submitted = (array) $this->input('permissions', []);
        $locked = RolePolicy::lockedPermissionsFor($this->user());

        return $this->user()->can('update', $this->route('role'))
            && array_intersect($submitted, $locked) === [];
    }

    /**
     * Aturan validasi ubah hak akses role.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'permissions.*.in' => 'Hak akses yang dipilih tidak valid.',
        ];
    }
}
