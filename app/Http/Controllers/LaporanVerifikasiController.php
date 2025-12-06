<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\LaporanVerifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaporanVerifikasiController extends Controller
{
    /**
     * Tampilkan daftar permohonan yang perlu dibuat laporan verifikasi
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'laporanVerifikasi'])
            ->where('status_akhir', 'selesai'); // Hanya yang sudah selesai verifikasi

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status laporan
        if ($request->filled('status_laporan')) {
            if ($request->status_laporan == 'belum_ada') {
                $query->doesntHave('laporanVerifikasi');
            } else {
                $query->has('laporanVerifikasi');
            }
        }

        $permohonan = $query->latest()->paginate(10);

        return view('laporan-verifikasi.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk membuat laporan verifikasi
     */
    public function create(Permohonan $permohonan)
    {
        // Cek apakah sudah ada laporan
        if ($permohonan->laporanVerifikasi) {
            return redirect()->route('laporan-verifikasi.show', $permohonan)
                ->with('info', 'Laporan verifikasi sudah dibuat sebelumnya.');
        }

        // Ambil data dokumen untuk statistik
        $dokumenStats = $permohonan->permohonanDokumen()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_verifikasi = 'verified' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN status_verifikasi = 'revision_required' THEN 1 ELSE 0 END) as revision
            ")
            ->first();

        return view('laporan-verifikasi.create', compact('permohonan', 'dokumenStats'));
    }

    /**
     * Simpan laporan verifikasi
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'ringkasan_verifikasi' => 'required|string',
            'catatan_admin' => 'nullable|string',
            'status_kelengkapan' => 'required|in:lengkap,tidak_lengkap',
        ]);

        try {
            DB::beginTransaction();

            // Hitung statistik dokumen
            $dokumenStats = $permohonan->permohonanDokumen()
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status_verifikasi = 'verified' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN status_verifikasi = 'revision_required' THEN 1 ELSE 0 END) as revision
                ")
                ->first();

            // Buat laporan verifikasi
            $laporan = LaporanVerifikasi::create([
                'permohonan_id' => $permohonan->id,
                'ringkasan_verifikasi' => $request->ringkasan_verifikasi,
                'catatan_admin' => $request->catatan_admin,
                'status_kelengkapan' => $request->status_kelengkapan,
                'jumlah_dokumen_verified' => $dokumenStats->verified ?? 0,
                'jumlah_dokumen_revision' => $dokumenStats->revision ?? 0,
                'total_dokumen' => $dokumenStats->total ?? 0,
                'dibuat_oleh' => Auth::id(),
                'tanggal_laporan' => now(),
            ]);

            // Update tahapan permohonan (opsional, skip jika error)
            try {
                $tahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
                if ($tahapanVerifikasi) {
                    $permohonan->tahapan()->updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanVerifikasi->id
                        ],
                        [
                            'status' => 'selesai',
                            'tgl_mulai' => $permohonan->submitted_at ?? now(),
                            'tgl_selesai' => now(),
                            'catatan' => 'Laporan verifikasi telah dibuat',
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Log error tapi tetap lanjutkan
                Log::warning('Gagal update tahapan: ' . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('laporan-verifikasi.show', $permohonan)
                ->with('success', 'Laporan verifikasi berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat laporan verifikasi: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Gagal membuat laporan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail laporan verifikasi
     */
    public function show(Permohonan $permohonan)
    {
        $laporan = $permohonan->laporanVerifikasi;

        if (!$laporan) {
            return redirect()->route('laporan-verifikasi.create', $permohonan)
                ->with('info', 'Laporan verifikasi belum dibuat.');
        }

        return view('laporan-verifikasi.show', compact('permohonan', 'laporan'));
    }

    /**
     * Download laporan verifikasi dalam format PDF
     */
    public function download(Permohonan $permohonan)
    {
        $laporan = $permohonan->laporanVerifikasi;

        if (!$laporan) {
            return back()->with('error', 'Laporan verifikasi tidak ditemukan.');
        }

        // TODO: Implement PDF generation
        // Untuk sementara redirect ke show
        return redirect()->route('laporan-verifikasi.show', $permohonan)
            ->with('info', 'Fitur download PDF akan segera tersedia.');
    }
}
