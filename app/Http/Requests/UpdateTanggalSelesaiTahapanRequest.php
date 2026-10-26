<?php

namespace App\Http\Requests;

use App\Enums\StatusPermohonan;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

/**
 * Validasi penyesuaian tanggal selesai tahapan (PRD #5, keputusan K2).
 *
 * Tanggal tidak boleh di masa depan, tidak sebelum permohonan dibuat, dan wajib urut terhadap
 * tahapan sebelum dan sesudahnya yang sudah selesai. Hanya tahapan berstatus selesai yang bisa diubah.
 * Tidak ada alasan perubahan; jejaknya ada di riwayat tahapan dan activity log.
 */
class UpdateTanggalSelesaiTahapanRequest extends FormRequest
{
    /**
     * Pengguna harus boleh melihat permohonan ini (permission ubah tanggal dijaga middleware route).
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->permohonan());
    }

    /**
     * Aturan validasi.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tanggal_selesai' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.$this->permohonan()->created_at->toDateString(),
            ],
        ];
    }

    /**
     * Pemeriksaan status tahapan dan urutan terhadap tahapan lain yang sudah selesai.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $tahapan = $this->permohonanTahapan();

                if ($tahapan->status !== StatusPermohonan::Selesai) {
                    $validator->errors()->add('tanggal_selesai', 'Hanya tahapan yang sudah selesai yang dapat diubah tanggal selesainya.');

                    return;
                }

                $tanggal = Carbon::parse($this->input('tanggal_selesai'))->toDateString();
                $urutan = $tahapan->masterTahapan->urutan;

                $selesaiLain = PermohonanTahapan::query()
                    ->where('permohonan_id', $tahapan->permohonan_id)
                    ->whereKeyNot($tahapan->getKey())
                    ->whereNotNull('tanggal_selesai')
                    ->with('masterTahapan')
                    ->get();

                $sebelum = $selesaiLain->filter(fn (PermohonanTahapan $item) => $item->masterTahapan->urutan < $urutan)
                    ->sortByDesc(fn (PermohonanTahapan $item) => $item->masterTahapan->urutan)
                    ->first();

                $sesudah = $selesaiLain->filter(fn (PermohonanTahapan $item) => $item->masterTahapan->urutan > $urutan)
                    ->sortBy(fn (PermohonanTahapan $item) => $item->masterTahapan->urutan)
                    ->first();

                if ($sebelum && $tanggal < $sebelum->tanggal_selesai->toDateString()) {
                    $validator->errors()->add('tanggal_selesai', sprintf(
                        'Tanggal selesai tidak boleh lebih awal dari tahapan %s (%s).',
                        $sebelum->masterTahapan->nama_tahapan,
                        $sebelum->tanggal_selesai->locale('id')->translatedFormat('d F Y'),
                    ));
                }

                if ($sesudah && $tanggal > $sesudah->tanggal_selesai->toDateString()) {
                    $validator->errors()->add('tanggal_selesai', sprintf(
                        'Tanggal selesai tidak boleh lebih akhir dari tahapan %s (%s).',
                        $sesudah->masterTahapan->nama_tahapan,
                        $sesudah->tanggal_selesai->locale('id')->translatedFormat('d F Y'),
                    ));
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
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date_format' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.before_or_equal' => 'Tanggal selesai tidak boleh di masa depan.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum permohonan dibuat.',
        ];
    }

    private function permohonan(): Permohonan
    {
        return $this->route('permohonan');
    }

    private function permohonanTahapan(): PermohonanTahapan
    {
        return $this->route('permohonanTahapan');
    }
}
