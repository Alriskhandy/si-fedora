<?php

namespace App\Http\Controllers\Fasilitasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTanggalSelesaiTahapanRequest;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use App\Models\PermohonanTahapanRiwayat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Penyesuaian tanggal selesai tahapan permohonan oleh Admin (PRD #5).
 *
 * Akses: tahapan.update-tanggal-selesai + hak lihat permohonan.
 * Tanggal selesai diisi otomatis oleh sistem saat tahapan selesai; Admin dapat mengubahnya di sini.
 * Setiap perubahan dicatat di riwayat tahapan dan activity log.
 * Setelah simpan, pengguna dikembalikan ke panel (#tanggal-selesai-tahapan).
 * Tampilan: panel "Tanggal Selesai Tahapan" di pages/fasilitasi/permohonan/show.
 */
class TanggalSelesaiTahapanController extends Controller
{
    /**
     * Simpan tanggal selesai baru.
     */
    public function update(UpdateTanggalSelesaiTahapanRequest $request, Permohonan $permohonan, PermohonanTahapan $permohonanTahapan): RedirectResponse
    {
        abort_unless($permohonanTahapan->permohonan_id === $permohonan->id, 404);

        $tanggalLama = $permohonanTahapan->tanggal_selesai;
        // Jam dari tanggal selesai lama dipertahankan agar tampilan "tanggal, jam" tetap bermakna
        $tanggalBaru = Carbon::parse($request->validated('tanggal_selesai'))
            ->setTimeFrom($tanggalLama ?? now()->startOfDay());
        $namaTahapan = $permohonanTahapan->masterTahapan->nama_tahapan;

        $kembali = redirect()->to(route('permohonan.show', $permohonan).'#tanggal-selesai-tahapan');

        if ($tanggalLama?->isSameDay($tanggalBaru)) {
            return $kembali->with('info', 'Tanggal selesai tidak berubah.');
        }

        DB::transaction(function () use ($request, $permohonanTahapan, $tanggalLama, $tanggalBaru): void {
            $permohonanTahapan->riwayat()->create([
                'jenis' => PermohonanTahapanRiwayat::JENIS_TANGGAL_SELESAI,
                'nilai_lama' => $tanggalLama,
                'nilai_baru' => $tanggalBaru,
                'user_id' => $request->user()->id,
            ]);

            $permohonanTahapan->update([
                'tanggal_selesai' => $tanggalBaru,
                'updated_by' => $request->user()->id,
            ]);
        });

        activity()
            ->performedOn($permohonan)
            ->causedBy($request->user())
            ->withProperties([
                'tahapan' => $namaTahapan,
                'tanggal_lama' => $tanggalLama?->toDateString(),
                'tanggal_baru' => $tanggalBaru->toDateString(),
            ])
            ->log('Mengubah tanggal selesai tahapan '.$namaTahapan);

        return $kembali->with('success', "Tanggal selesai tahapan {$namaTahapan} berhasil diperbarui.");
    }
}
