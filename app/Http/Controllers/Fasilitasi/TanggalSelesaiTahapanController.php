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
 * Setiap perubahan dicatat di riwayat tahapan, kecuali koreksi pertama atas tanggal perkiraan
 * dari data lama (keputusan K3). Semua perubahan juga dicatat di activity log.
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
        $tanggalBaru = Carbon::parse($request->validated('tanggal_selesai'))->startOfDay();
        $namaTahapan = $permohonanTahapan->masterTahapan->nama_tahapan;

        if (! $permohonanTahapan->tanggal_selesai_perkiraan && $tanggalLama?->isSameDay($tanggalBaru)) {
            return redirect()->route('permohonan.show', $permohonan)->with('info', 'Tanggal selesai tidak berubah.');
        }

        DB::transaction(function () use ($request, $permohonanTahapan, $tanggalLama, $tanggalBaru): void {
            if (! $permohonanTahapan->tanggal_selesai_perkiraan) {
                $permohonanTahapan->riwayat()->create([
                    'jenis' => PermohonanTahapanRiwayat::JENIS_TANGGAL_SELESAI,
                    'nilai_lama' => $tanggalLama,
                    'nilai_baru' => $tanggalBaru,
                    'alasan' => $request->validated('alasan'),
                    'user_id' => $request->user()->id,
                ]);
            }

            $permohonanTahapan->update([
                'tanggal_selesai' => $tanggalBaru,
                'tanggal_selesai_perkiraan' => false,
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
                'alasan' => $request->validated('alasan'),
            ])
            ->log('Mengubah tanggal selesai tahapan '.$namaTahapan);

        return redirect()->route('permohonan.show', $permohonan)
            ->with('success', "Tanggal selesai tahapan {$namaTahapan} berhasil diperbarui.");
    }
}
