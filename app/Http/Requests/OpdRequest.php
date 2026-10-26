<?php

namespace App\Http\Requests;

use App\Models\MasterUrusan;
use App\Models\Opd;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi tambah & ubah master OPD (OpdController::store/update), sesuai kamus data OPD.
 */
class OpdRequest extends FormRequest
{
    /**
     * Aturan validasi OPD.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $opd = $this->route('opd');

        return [
            'kode_wilayah' => ['required', 'regex:/^\d{2}$/'],
            'kode_opd' => [
                'required',
                'max:20',
                'regex:'.Opd::KODE_PATTERN,
                'starts_with:'.$this->input('kode_wilayah').'.',
                Rule::unique('opd', 'kode_opd')->ignore($opd),
            ],
            'nama_opd' => ['required', 'string', 'max:150', Rule::unique('opd', 'nama_opd')->ignore($opd)],
            'singkatan' => ['required', 'string', 'max:20'],
            'jenis_opd' => ['required', Rule::in(Opd::JENIS_OPD)],
            'rumpun_urusan' => ['required', Rule::in(MasterUrusan::rumpunList())],
            'status' => ['required', Rule::in(Opd::STATUS)],
            'tanggal_update' => ['required', 'date_format:Y-m-d'],
            'kode_urusan' => ['nullable', 'string', 'max:255'],
            'urusan_pemerintahan' => ['nullable', 'string', 'max:255'],
            'induk_opd' => ['nullable', 'regex:'.Opd::KODE_PATTERN, 'different:kode_opd', Rule::exists('opd', 'kode_opd')],
        ];
    }

    /**
     * Setiap kode urusan yang diampu (dipisah ;) berformat X.XX dan terdaftar di master urusan,
     * karena kode ini menentukan urusan yang boleh diberi masukan oleh akun OPD.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $salah = array_filter(
                    MasterUrusan::splitDaftarKode($this->input('kode_urusan')),
                    fn (string $kode) => ! preg_match('/^[1-8]\.\d{2}$/', $kode),
                );

                if ($salah !== []) {
                    $validator->errors()->add('kode_urusan', 'Format kode urusan harus X.XX (lebih dari satu dipisah ;): '.implode(', ', $salah).'.');

                    return;
                }

                $kodes = MasterUrusan::splitDaftarKode($this->input('kode_urusan'));
                $tidakTerdaftar = array_diff($kodes, MasterUrusan::whereIn('kode_urusan', $kodes)->pluck('kode_urusan')->all());

                if ($tidakTerdaftar !== []) {
                    $validator->errors()->add('kode_urusan', 'Kode urusan tidak terdaftar di master urusan: '.implode(', ', $tidakTerdaftar).'.');
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
            'kode_wilayah.required' => 'Kode wilayah wajib diisi.',
            'kode_wilayah.regex' => 'Kode wilayah harus 2 digit, misalnya 82.',
            'kode_opd.required' => 'Kode OPD wajib diisi.',
            'kode_opd.regex' => 'Format kode OPD harus 82.XX.XX atau 82.XX.XX.XX.',
            'kode_opd.max' => 'Kode OPD maksimal 20 karakter.',
            'kode_opd.starts_with' => 'Kode OPD harus diawali kode wilayah.',
            'kode_opd.unique' => 'Kode OPD sudah terdaftar.',
            'nama_opd.required' => 'Nama OPD wajib diisi.',
            'nama_opd.max' => 'Nama OPD maksimal 150 karakter.',
            'nama_opd.unique' => 'Nama OPD sudah terdaftar.',
            'singkatan.required' => 'Singkatan wajib diisi.',
            'singkatan.max' => 'Singkatan maksimal 20 karakter.',
            'jenis_opd.required' => 'Jenis OPD wajib dipilih.',
            'jenis_opd.in' => 'Jenis OPD tidak valid.',
            'rumpun_urusan.required' => 'Rumpun urusan wajib dipilih.',
            'rumpun_urusan.in' => 'Rumpun urusan tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'tanggal_update.required' => 'Tanggal pemutakhiran wajib diisi.',
            'tanggal_update.date_format' => 'Format tanggal pemutakhiran tidak valid.',
            'induk_opd.regex' => 'Format kode OPD induk harus 82.XX.XX atau 82.XX.XX.XX.',
            'induk_opd.different' => 'OPD induk tidak boleh OPD itu sendiri.',
            'induk_opd.exists' => 'Kode OPD induk tidak terdaftar.',
        ];
    }
}
