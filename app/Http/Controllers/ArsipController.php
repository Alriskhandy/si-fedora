<?php

namespace App\Http\Controllers;

use App\Models\MasterJenisDokumen;
use App\Models\Permohonan;
use Illuminate\Http\Request;

class ArsipController extends Controller
{
    /**
     * Display archive of all permohonan
     */
    public function index(Request $request)
    {
        // Get all jenis dokumen with count of permohonan
        $jenisDokumenList = MasterJenisDokumen::withCount(['permohonan' => function($query) {
            $query->where('status_akhir', 'selesai'); // Only count completed documents
        }])
        ->orderBy('nama')
        ->get();

        return view('pages.arsip.index', compact('jenisDokumenList'));
    }

    /**
     * Display list of permohonan by jenis dokumen
     */
    public function listByJenis(Request $request, $jenisDokumenId)
    {
        $jenisDokumen = MasterJenisDokumen::findOrFail($jenisDokumenId);
        
        $query = Permohonan::with([
            'kabupatenKota', 
            'jenisDokumen', 
            'tahapanAktif.masterTahapan',
            'pemohon'
        ])
        ->where('jenis_dokumen_id', $jenisDokumenId)
        ->where('status_akhir', 'selesai');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('kabupatenKota', function($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orWhere('tahun', 'like', '%' . $search . '%');
            });
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $permohonan = $query->latest()->paginate(15);
        
        // Get filter options
        $tahunList = Permohonan::where('jenis_dokumen_id', $jenisDokumenId)
            ->where('status_akhir', 'selesai')
            ->selectRaw('DISTINCT tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('pages.arsip.list', compact('permohonan', 'jenisDokumen', 'tahunList'));
    }

    /**
     * Display all documents for a specific permohonan
     */
    public function show(Permohonan $permohonan)
    {
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
            'suratRekomendasi',
            'perpanjanganWaktu',
            'tindakLanjut',
            'penetapanPerda'
        ]);

        // Count documents by type
        $documentCounts = [
            'dokumen_permohonan' => $permohonan->permohonanDokumen ? $permohonan->permohonanDokumen->count() : 0,
            'dokumen_tahapan' => $permohonan->dokumenTahapan ? $permohonan->dokumenTahapan->count() : 0,
            'laporan_verifikasi' => $permohonan->laporanVerifikasi ? 1 : 0,
            'hasil_fasilitasi' => $permohonan->hasilFasilitasi ? 1 : 0,
            'jadwal_fasilitasi' => $permohonan->jadwalFasilitasi ? 1 : 0,
            'undangan_pelaksanaan' => \App\Models\UndanganPelaksanaan::where('permohonan_id', $permohonan->id)->count(),
            'surat_pemberitahuan' => 0, // Surat pemberitahuan related to jadwal, not permohonan
            'surat_rekomendasi' => $permohonan->suratRekomendasi ? 1 : 0,
            'surat_penyampaian_hasil' => ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->surat_penyampaian) ? 1 : 0,
            'perpanjangan_waktu' => $permohonan->perpanjanganWaktu ? $permohonan->perpanjanganWaktu->count() : 0,
            'tindak_lanjut' => $permohonan->tindakLanjut ? 1 : 0,
            'penetapan_perda' => $permohonan->penetapanPerda ? 1 : 0,
        ];

        // Get surat pemberitahuan if jadwal exists
        if ($permohonan->jadwalFasilitasi) {
            $documentCounts['surat_pemberitahuan'] = \App\Models\SuratPemberitahuan::where('jadwal_fasilitasi_id', $permohonan->jadwalFasilitasi->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->count();
        }

        // Get undangan pelaksanaan
        $undanganPelaksanaan = \App\Models\UndanganPelaksanaan::where('permohonan_id', $permohonan->id)->get();
        $permohonan->setRelation('undanganPelaksanaan', $undanganPelaksanaan);

        return view('pages.arsip.show', compact('permohonan', 'documentCounts'));
    }
}
