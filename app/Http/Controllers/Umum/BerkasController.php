<?php

namespace App\Http\Controllers\Umum;

use App\Http\Controllers\Controller;
use App\Models\DokumenTahapan;
use App\Models\HasilFasilitasi;
use App\Models\LaporanVerifikasi;
use App\Models\PermohonanDokumen;
use App\Models\PerpanjanganWaktu;
use App\Models\UndanganPelaksanaan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menyajikan dokumen dari disk privat `dokumen` setelah memeriksa hak akses.
 *
 * Akses: mengikuti PermohonanPolicy::downloadDokumen (atau PerpanjanganWaktuPolicy::view untuk perpanjangan tanpa permohonan).
 * Jenis berkas yang boleh diakses didaftarkan di konstanta JENIS.
 */
class BerkasController extends Controller
{
    /**
     * Jenis berkas yang boleh diakses: model, kolom path, dan kolom nama asli (opsional).
     *
     * @var array<string, array{model: class-string<Model>, path: string, name: string|null}>
     */
    private const JENIS = [
        'permohonan-dokumen' => ['model' => PermohonanDokumen::class, 'path' => 'file_path', 'name' => 'file_name'],
        'dokumen-tahapan' => ['model' => DokumenTahapan::class, 'path' => 'file_path', 'name' => 'file_name'],
        'laporan-verifikasi' => ['model' => LaporanVerifikasi::class, 'path' => 'file_laporan', 'name' => null],
        'undangan-pelaksanaan' => ['model' => UndanganPelaksanaan::class, 'path' => 'file_undangan', 'name' => null],
        'hasil-fasilitasi-final' => ['model' => HasilFasilitasi::class, 'path' => 'draft_final_file', 'name' => null],
        'perpanjangan-waktu' => ['model' => PerpanjanganWaktu::class, 'path' => 'surat_permohonan', 'name' => null],
    ];

    /**
     * Tampilkan berkas (inline), atau unduh bila `?unduh=1`.
     */
    public function show(Request $request, string $jenis, int $id): StreamedResponse
    {
        $definisi = self::JENIS[$jenis] ?? abort(404);

        /** @var Model $record */
        $record = $definisi['model']::findOrFail($id);

        $this->authorizeRecord($record);

        $path = $record->{$definisi['path']};
        $disk = Storage::disk('dokumen');

        abort_if(blank($path) || ! $disk->exists($path), 404, 'Berkas tidak ditemukan.');

        $namaBerkas = $definisi['name'] ? ($record->{$definisi['name']} ?: basename($path)) : basename($path);

        return $request->boolean('unduh')
            ? $disk->download($path, $namaBerkas)
            : $disk->response($path, $namaBerkas);
    }

    private function authorizeRecord(Model $record): void
    {
        $permohonan = $record->permohonan;

        if ($permohonan) {
            Gate::authorize('downloadDokumen', $permohonan);

            return;
        }

        // Perpanjangan batas permohonan (berbasis jadwal) tidak terkait permohonan
        if ($record instanceof PerpanjanganWaktu) {
            Gate::authorize('view', $record);

            return;
        }

        abort(404);
    }
}
