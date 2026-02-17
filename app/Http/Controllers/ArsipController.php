<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;

class ArsipController extends Controller
{
    /**
     * Display archive of all permohonan
     */
    public function index(Request $request)
    {
        $query = Permohonan::with([
            'kabupatenKota', 
            'jenisDokumen', 
            'tahapanAktif.masterTahapan',
            'pemohon'
        ]);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('kabupatenKota', function($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orWhereHas('jenisDokumen', function($q) use ($search) {
                    $q->where('nama_dokumen', 'like', '%' . $search . '%');
                })
                ->orWhere('tahun', 'like', '%' . $search . '%');
            });
        }

        // Filter by jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Filter by status
        if ($request->filled('status_akhir')) {
            $query->where('status_akhir', $request->status_akhir);
        }

        $permohonan = $query->latest()->paginate(15);
        
        // Get filter options
        $jenisDokumenList = \App\Models\MasterJenisDokumen::orderBy('nama')->get();
        $tahunList = Permohonan::selectRaw('DISTINCT tahun')->orderByDesc('tahun')->pluck('tahun');

        return view('pages.arsip.index', compact('permohonan', 'jenisDokumenList', 'tahunList'));
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
