<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\LaporanVerifikasi;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\MasterTahapan;
use App\Models\PermohonanTahapan;
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
        // Cek apakah sudah ada laporan dengan status lengkap
        if ($permohonan->laporanVerifikasi && $permohonan->laporanVerifikasi->status_kelengkapan === 'lengkap') {
            return redirect()->route('laporan-verifikasi.show', $permohonan)
                ->with('info', 'Laporan verifikasi sudah dibuat dan berstatus lengkap.');
        }

        // Ambil data dokumen untuk statistik
        $dokumenStats = $permohonan->permohonanDokumen()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_verifikasi = 'verified' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN status_verifikasi = 'revision' THEN 1 ELSE 0 END) as revision
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
            'status_kelengkapan' => 'required|in:lengkap,tidak_lengkap',
        ]);

        try {
            DB::beginTransaction();

            // Load relasi
            $permohonan->load(['kabupatenKota', 'jenisDokumen']);

            // Hitung statistik dokumen
            $dokumenStats = $permohonan->permohonanDokumen()
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status_verifikasi = 'verified' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN status_verifikasi = 'revision' THEN 1 ELSE 0 END) as revision
                ")
                ->first();

            // Jika sudah ada laporan dengan status tidak_lengkap, update. Jika lengkap atau belum ada, create
            $existingLaporan = $permohonan->laporanVerifikasi;

            if ($existingLaporan && $existingLaporan->status_kelengkapan === 'tidak_lengkap') {
                // Update laporan yang sudah ada
                $existingLaporan->update([
                    'ringkasan_verifikasi' => $request->ringkasan_verifikasi,
                    'status_kelengkapan' => $request->status_kelengkapan,
                    'jumlah_dokumen_verified' => $dokumenStats->verified ?? 0,
                    'jumlah_dokumen_revision' => $dokumenStats->revision ?? 0,
                    'total_dokumen' => $dokumenStats->total ?? 0,
                    'dibuat_oleh' => Auth::id(),
                    'tanggal_laporan' => now(),
                ]);
                $laporan = $existingLaporan;
            } else {
                // Buat laporan verifikasi baru
                $laporan = LaporanVerifikasi::create([
                    'permohonan_id' => $permohonan->id,
                    'ringkasan_verifikasi' => $request->ringkasan_verifikasi,
                    'status_kelengkapan' => $request->status_kelengkapan,
                    'jumlah_dokumen_verified' => $dokumenStats->verified ?? 0,
                    'jumlah_dokumen_revision' => $dokumenStats->revision ?? 0,
                    'total_dokumen' => $dokumenStats->total ?? 0,
                    'dibuat_oleh' => Auth::id(),
                    'tanggal_laporan' => now(),
                ]);
            }

            // Update tahapan Verifikasi menjadi selesai dan aktifkan tahapan berikutnya
            $this->updateTahapanVerifikasi($permohonan);

            // Activity log
            $this->logLaporanVerifikasi($permohonan, $laporan);

            // Kirim notifikasi
            $this->sendNotifications($permohonan, $laporan);

            DB::commit();

            return redirect()->route('permohonan.show', $permohonan)
                ->with('success', 'Laporan verifikasi berhasil disimpan. Tahapan verifikasi selesai.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat laporan verifikasi: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Gagal membuat laporan: ' . $e->getMessage());
        }
    }

    /**
     * Update tahapan verifikasi menjadi selesai dan aktifkan tahapan berikutnya
     */
    private function updateTahapanVerifikasi($permohonan)
    {
        try {
            $tahapanVerifikasi = MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
            
            if ($tahapanVerifikasi) {
                // Update tahapan Verifikasi menjadi selesai
                PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $tahapanVerifikasi->id
                    ],
                    [
                        'status' => 'selesai',
                        'catatan' => 'Laporan verifikasi telah dibuat oleh admin pada ' . now()->format('d M Y H:i'),
                        'updated_by' => Auth::id(),
                    ]
                );

                // Aktifkan tahapan berikutnya
                $this->activateNextTahapan($permohonan, $tahapanVerifikasi);
            }
        } catch (\Exception $e) {
            Log::warning('Gagal update tahapan: ' . $e->getMessage());
        }
    }

    /**
     * Aktifkan tahapan berikutnya setelah verifikasi selesai
     */
    private function activateNextTahapan($permohonan, $currentTahapan)
    {
        // Cari tahapan berikutnya berdasarkan urutan
        $nextTahapan = MasterTahapan::where('urutan', '>', $currentTahapan->urutan)
            ->orderBy('urutan', 'asc')
            ->first();

        if (!$nextTahapan) {
            return;
        }

        // Buat tahapan berikutnya dengan status 'proses'
        PermohonanTahapan::updateOrCreate(
            [
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $nextTahapan->id,
            ],
            [
                'status' => 'proses',
                'catatan' => 'Tahapan dimulai setelah laporan verifikasi dibuat',
                'updated_by' => Auth::id(),
            ]
        );

        Log::info('Tahapan berikutnya diaktifkan', [
            'permohonan_id' => $permohonan->id,
            'tahapan' => $nextTahapan->nama_tahapan,
        ]);
    }

    /**
     * Log aktivitas pembuatan laporan verifikasi
     */
    private function logLaporanVerifikasi($permohonan, $laporan)
    {
        activity()
            ->performedOn($permohonan)
            ->causedBy(auth()->user())
            ->withProperties([
                'laporan_id' => $laporan->id,
                'status_kelengkapan' => $laporan->status_kelengkapan,
                'total_dokumen' => $laporan->total_dokumen,
                'dokumen_verified' => $laporan->jumlah_dokumen_verified,
                'dokumen_revision' => $laporan->jumlah_dokumen_revision,
                'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? null,
                'jenis_dokumen' => $permohonan->jenisDokumen->nama_dokumen ?? null,
                'tahun' => $permohonan->tahun,
            ])
            ->log('Laporan verifikasi dibuat oleh admin');

        Log::info('Laporan verifikasi dibuat', [
            'permohonan_id' => $permohonan->id,
            'admin' => auth()->user()->name,
            'status_kelengkapan' => $laporan->status_kelengkapan,
        ]);
    }

    /**
     * Kirim notifikasi ke kaban, pemohon, dan tim fedora
     */
    private function sendNotifications($permohonan, $laporan)
    {
        // Notifikasi ke Kaban untuk penetapan jadwal
        $kaban = User::role('kaban')->get();
        
        foreach ($kaban as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'title' => 'Penetapan Jadwal Diperlukan',
                'message' => sprintf(
                    'Verifikasi untuk %s - Kab/Kota %s tahun %s telah selesai dengan status %s. Silakan tetapkan jadwal pelaksanaan fasilitasi.',
                    $permohonan->jenisDokumen->nama_dokumen ?? 'N/A',
                    $permohonan->kabupatenKota->nama ?? 'N/A',
                    $permohonan->tahun,
                    $laporan->status_kelengkapan == 'lengkap' ? 'Lengkap' : 'Tidak Lengkap'
                ),
                'type' => 'info',
                'action_url' => route('permohonan.show', $permohonan),
                'notifiable_type' => Permohonan::class,
                'notifiable_id' => $permohonan->id,
            ]);
        }

        // Notifikasi ke Pemohon
        Notifikasi::create([
            'user_id' => $permohonan->user_id,
            'title' => 'Laporan Verifikasi Selesai',
            'message' => sprintf(
                'Laporan verifikasi untuk permohonan Anda (%s - Kab/Kota %s tahun %s) telah dibuat dengan status %s. Proses akan dilanjutkan ke tahap berikutnya.',
                $permohonan->jenisDokumen->nama_dokumen ?? 'N/A',
                $permohonan->kabupatenKota->nama ?? 'N/A',
                $permohonan->tahun,
                $laporan->status_kelengkapan == 'lengkap' ? 'Lengkap' : 'Tidak Lengkap'
            ),
            'type' => $laporan->status_kelengkapan == 'lengkap' ? 'success' : 'warning',
            'action_url' => route('permohonan.show', $permohonan),
            'notifiable_type' => Permohonan::class,
            'notifiable_id' => $permohonan->id,
        ]);

        // Notifikasi ke Tim Fedora (Verifikator dan Fasilitator)
        $timFedora = User::role(['verifikator', 'fasilitator'])->get();
        
        foreach ($timFedora as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'title' => 'Laporan Verifikasi Dibuat',
                'message' => sprintf(
                    'Laporan verifikasi untuk %s - Kab/Kota %s tahun %s telah dibuat oleh admin dengan status %s.',
                    $permohonan->jenisDokumen->nama_dokumen ?? 'N/A',
                    $permohonan->kabupatenKota->nama ?? 'N/A',
                    $permohonan->tahun,
                    $laporan->status_kelengkapan == 'lengkap' ? 'Lengkap' : 'Tidak Lengkap'
                ),
                'type' => 'info',
                'action_url' => route('permohonan.show', $permohonan),
                'notifiable_type' => Permohonan::class,
                'notifiable_id' => $permohonan->id,
            ]);
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
