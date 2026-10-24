<?php

namespace App\Http\Controllers\Umum;

use App\Http\Controllers\Controller;
use App\Models\MasterJenisDokumen;
use App\Models\Permohonan;
use App\Models\SuratPemberitahuan;
use App\Models\UndanganPelaksanaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Arsip seluruh dokumen permohonan per jenis dokumen. Daftar dibatasi scope Permohonan::visibleTo, detail oleh PermohonanPolicy.
 *
 * Akses: semua pengguna login (dibatasi policy).
 * View: resources/views/pages/umum/arsip
 */
class ArsipController extends Controller
{
    /**
     * Daftar jenis dokumen beserta jumlah permohonan yang terlihat oleh pengguna.
     */
    public function index(Request $request)
    {
        // Get all jenis dokumen with count of permohonan
        $jenisDokumenList = MasterJenisDokumen::withCount(['permohonan' => function ($query) use ($request) {
            $query->whereIn('status_akhir', ['proses', 'revisi', 'selesai']) // Permohonan yang sudah diajukan
                ->visibleTo($request->user());
        }])
            ->orderBy('nama')
            ->get();

        return view('pages.umum.arsip.index', compact('jenisDokumenList'));
    }

    /**
     * Daftar permohonan per jenis dokumen (hanya yang sudah diajukan), dengan pencarian dan filter tahun.
     */
    public function listByJenis(Request $request, $jenisDokumenId)
    {
        $jenisDokumen = MasterJenisDokumen::findOrFail($jenisDokumenId);

        $query = Permohonan::with([
            'kabupatenKota',
            'jenisDokumen',
            'tahapanAktif.masterTahapan',
            'pemohon',
        ])
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->whereIn('status_akhir', ['proses', 'revisi', 'selesai'])
            ->visibleTo($request->user());

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('kabupatenKota', function ($q) use ($search) {
                    $q->where('nama', 'like', '%'.$search.'%');
                })
                    ->orWhere('tahun', 'like', '%'.$search.'%');
            });
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $permohonan = $query->latest()->paginate(15);

        // Get filter options
        $tahunList = Permohonan::where('jenis_dokumen_id', $jenisDokumenId)
            ->whereIn('status_akhir', ['proses', 'revisi', 'selesai'])
            ->visibleTo($request->user())
            ->selectRaw('DISTINCT tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('pages.umum.arsip.list', compact('permohonan', 'jenisDokumen', 'tahunList'));
    }

    /**
     * Seluruh dokumen satu permohonan, dikelompokkan per tahapan.
     */
    public function show(Permohonan $permohonan)
    {
        Gate::authorize('view', $permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'pemohon',
            'tahapanAktif.masterTahapan',
            'permohonanDokumen.masterKelengkapan',
            'dokumenTahapan',
            'laporanVerifikasi',
            'hasilFasilitasi',
            'jadwalFasilitasi',
            'perpanjanganWaktu',
            'tindakLanjut',
        ]);

        // Count documents by type
        $documentCounts = [
            'dokumen_permohonan' => $permohonan->permohonanDokumen ? $permohonan->permohonanDokumen->count() : 0,
            'dokumen_tahapan' => $permohonan->dokumenTahapan ? $permohonan->dokumenTahapan->count() : 0,
            'laporan_verifikasi' => $permohonan->laporanVerifikasi ? 1 : 0,
            'hasil_fasilitasi' => $permohonan->hasilFasilitasi ? 1 : 0,
            'jadwal_fasilitasi' => $permohonan->jadwalFasilitasi ? 1 : 0,
            'undangan_pelaksanaan' => UndanganPelaksanaan::where('permohonan_id', $permohonan->id)->count(),
            'surat_pemberitahuan' => 0, // Surat pemberitahuan related to jadwal, not permohonan
            'surat_penyampaian_hasil' => ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->surat_penyampaian) ? 1 : 0,
            'perpanjangan_waktu' => $permohonan->perpanjanganWaktu ? $permohonan->perpanjanganWaktu->count() : 0,
            'tindak_lanjut' => $permohonan->tindakLanjut ? 1 : 0,
        ];

        // Get surat pemberitahuan if jadwal exists
        if ($permohonan->jadwalFasilitasi) {
            $documentCounts['surat_pemberitahuan'] = SuratPemberitahuan::where('jadwal_fasilitasi_id', $permohonan->jadwalFasilitasi->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->count();
        }

        // Get undangan pelaksanaan
        $undanganPelaksanaan = UndanganPelaksanaan::where('permohonan_id', $permohonan->id)->get();
        $permohonan->setRelation('undanganPelaksanaan', $undanganPelaksanaan);

        return view('pages.umum.arsip.show', compact('permohonan', 'documentCounts'));
    }
}
