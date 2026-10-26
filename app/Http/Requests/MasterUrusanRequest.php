<?php

namespace App\Http\Requests;

use App\Models\MasterUrusan;
use App\Models\Opd;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi tambah & ubah master urusan (MasterUrusanController::store/update), sesuai kamus data urusan,
 * ditambah penanda tampil di bagian urusan hasil fasilitasi.
 * Kelompok, nama kelompok, rumpun, dan singkatan OPD pengampu diisi otomatis oleh model.
 */
class MasterUrusanRequest extends FormRequest
{
    /**
     * Aturan validasi urusan.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode_urusan' => [
                'required',
                'regex:/^[1-8]\.\d{2}$/',
                Rule::unique('master_urusan', 'kode_urusan')->ignore($this->route('masterUrusan')),
            ],
            'nama_urusan' => ['required', 'string', 'max:150'],
            'kode_opd_pengampu' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(MasterUrusan::STATUS)],
            'tanggal_update' => ['required', 'date_format:Y-m-d'],
            'tampil_hasil_fasilitasi' => ['boolean'],
        ];
    }

    /**
     * Setiap kode OPD pengampu harus berformat 82.XX.XX dan terdaftar di master OPD.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $kodes = MasterUrusan::splitDaftarKode($this->input('kode_opd_pengampu'));

                $salahFormat = array_filter($kodes, fn (string $kode) => ! preg_match(Opd::KODE_PATTERN, $kode));
                if ($salahFormat !== []) {
                    $validator->errors()->add('kode_opd_pengampu', 'Format kode OPD pengampu harus 82.XX.XX atau 82.XX.XX.XX: '.implode(', ', $salahFormat).'.');

                    return;
                }

                $tidakTerdaftar = array_diff($kodes, Opd::whereIn('kode_opd', $kodes)->pluck('kode_opd')->all());
                if ($tidakTerdaftar !== []) {
                    $validator->errors()->add('kode_opd_pengampu', 'Kode OPD pengampu tidak terdaftar: '.implode(', ', $tidakTerdaftar).'.');
                }
            },
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
            'kode_urusan.required' => 'Kode urusan wajib diisi.',
            'kode_urusan.regex' => 'Format kode urusan harus X.XX dengan X = kelompok 1–8, misalnya 1.01.',
            'kode_urusan.unique' => 'Kode urusan sudah terdaftar.',
            'nama_urusan.required' => 'Nama urusan wajib diisi.',
            'nama_urusan.max' => 'Nama urusan maksimal 150 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'tanggal_update.required' => 'Tanggal pemutakhiran wajib diisi.',
            'tanggal_update.date_format' => 'Format tanggal pemutakhiran tidak valid.',
        ];
    }
}
