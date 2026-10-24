<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi perubahan profil. Nomor WhatsApp wajib format internasional (diawali 62).
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Aturan validasi profil.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'no_hp' => ['nullable', 'string', 'regex:/^62[0-9]{9,13}$/'],
            'kabupaten_kota_id' => ['nullable', 'exists:kabupaten_kota,id'],
            'foto_profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // Max 2MB
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Format nomor WhatsApp tidak valid. Harus dimulai dengan 62 dan 9-13 digit. Contoh: 628123456789',
        ];
    }
}
