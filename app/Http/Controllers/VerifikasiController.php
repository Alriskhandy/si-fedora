<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PersyaratanDokumen;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\MasterTahapan;
use App\Models\PermohonanTahapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifikasiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'permohonanDokumen.masterKelengkapan'])
            ->whereIn('status_akhir', ['proses', 'revisi', 'selesai']); // Tampilkan semua status verifikasi

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('kabupatenKota', function ($subQ) use ($request) {
                    $subQ->where('nama', 'like', '%' . $request->search . '%');
                })
                    ->orWhere('jenis_dokumen', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status verifikasi
        if ($request->filled('status')) {
            $query->where('status_akhir', $request->status);
        }

        $permohonan = $query->latest('submitted_at')->paginate(10);

        return view('verifikasi.index', compact('permohonan'));
    }

    public function show(Permohonan $permohonan)
    {
        // Load data lengkap dengan proper relations
        $permohonan->load([
            'kabupatenKota',
            'jadwalFasilitasi',
            'permohonanDokumen.masterKelengkapan'
        ]);

        return view('verifikasi.show', compact('permohonan'));
    }

    public function verifikasi(Request $request, Permohonan $permohonan)
    {
        // Validasi
        $request->validate([
            'dokumen' => 'required|array',
            'dokumen.*.status_verifikasi' => 'required|in:verified,revision',
            'catatan_umum' => 'nullable|string',
            'status_verifikasi' => 'required|in:verified,revision',
        ]);

        // Update dokumen verifikasi
        $allVerified = true;
        foreach ($request->dokumen as $dokumenId => $data) {
            $dokumen = PermohonanDokumen::findOrFail($dokumenId);
            $dokumen->update([
                'status_verifikasi' => $data['status_verifikasi'],
                'catatan_verifikasi' => $data['catatan'] ?? null,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            if ($data['status_verifikasi'] === 'revision') {
                $allVerified = false;
            }
        }

        // Update status permohonan berdasarkan hasil verifikasi
        $newStatus = $request->status_verifikasi === 'verified' && $allVerified ? 'selesai' : 'revisi';

        $permohonan->update([
            'status_akhir' => $newStatus,
        ]);

        $message = $newStatus === 'selesai'
            ? 'Verifikasi berhasil! Dokumen lengkap dan dapat dilanjutkan ke evaluasi.'
            : 'Verifikasi selesai! Dokumen perlu revisi oleh pemohon.';

        return redirect()->route('verifikasi.index')->with('success', $message);
    }

    /**
     * Verifikasi per dokumen (AJAX)
     */
    public function verifikasiDokumen(Request $request, Permohonan $permohonan)
    {
        // Validasi
        $request->validate([
            'dokumen_id' => 'required|exists:permohonan_dokumen,id',
            'status_verifikasi' => 'required|in:verified,revision',
            'catatan' => 'nullable|string',
        ]);

        // Cari dokumen
        $dokumen = PermohonanDokumen::where('id', $request->dokumen_id)
            ->where('permohonan_id', $permohonan->id)
            ->firstOrFail();

        // Update status verifikasi dokumen
        $dokumen->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_verifikasi' => $request->catatan,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Jika status = revision, reset file agar pemohon upload ulang
        if ($request->status_verifikasi === 'revision') {
            $dokumen->update([
                'file_path' => null,
                'file_name' => null,
                'is_ada' => false,
            ]);
        }

        // Cek apakah semua dokumen sudah verified
        $totalDokumen = $permohonan->permohonanDokumen->count();
        $verifiedDokumen = $permohonan->permohonanDokumen
            ->where('status_verifikasi', 'verified')
            ->count();
        $revisiDokumen = $permohonan->permohonanDokumen
            ->where('status_verifikasi', 'revision')
            ->count();

        // Update status permohonan dan tahapan
        if ($verifiedDokumen === $totalDokumen) {
            // Semua dokumen verified
            $permohonan->update(['status_akhir' => 'selesai']);

            // Update/create tahapan Verifikasi di permohonan_tahapan
            $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();

            if ($masterTahapanVerifikasi) {
                \App\Models\PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanVerifikasi->id,
                    ],
                    [
                        'status' => 'selesai',
                        'catatan' => 'Verifikasi dokumen selesai - semua dokumen terverifikasi pada ' . now()->format('d M Y H:i'),
                        'updated_by' => Auth::id(),
                    ]
                );
            }
        } elseif ($revisiDokumen > 0) {
            // Ada dokumen yang perlu revisi
            $permohonan->update(['status_akhir' => 'revisi']);

            // Update tahapan Verifikasi menjadi status revisi
            $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();

            if ($masterTahapanVerifikasi) {
                \App\Models\PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanVerifikasi->id,
                    ],
                    [
                        'status' => 'revisi',
                        'catatan' => 'Dokumen perlu revisi - pemohon diminta memperbaiki dokumen pada ' . now()->format('d M Y H:i'),
                        'updated_by' => Auth::id(),
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi dokumen berhasil disimpan',
            'data' => [
                'status_verifikasi' => $dokumen->status_verifikasi,
                'catatan' => $dokumen->catatan_verifikasi,
                'status_permohonan' => $permohonan->status_akhir,
            ]
        ]);
    }

    public function submit(Request $request, Permohonan $permohonan)
    {
        try {
            DB::beginTransaction();
            
            $verifications = $request->input('verifications', []);
            
            if (empty($verifications)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data verifikasi tidak ditemukan'
                ], 400);
            }
            
            // Update dokumen dan hitung statistik
            $totalVerified = 0;
            $totalRevision = 0;
            
            foreach ($verifications as $verification) {
                $dokumen = PermohonanDokumen::findOrFail($verification['id']);
                $dokumen->update([
                    'status_verifikasi' => $verification['status'],
                    'catatan_verifikasi' => $verification['catatan'] ?? null,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ]);
                
                $verification['status'] === 'verified' ? $totalVerified++ : $totalRevision++;
            }
            
            // Load relasi
            $permohonan->load(['kabupatenKota', 'jenisDokumen']);
            
            // Update status permohonan
            $hasRevision = $totalRevision > 0;
            $newStatus = $hasRevision ? 'revisi' : 'selesai';
            
            $permohonan->update(['status_akhir' => $newStatus]);
            
            // Update tahapan Verifikasi
            $this->updateTahapanVerifikasi($permohonan, $hasRevision, $totalVerified, $totalRevision);
            
            // Activity log
            $this->logVerifikasi($permohonan, $verifications, $totalVerified, $totalRevision, $newStatus);
            
            // Kirim notifikasi
            $this->sendNotifications($permohonan, $hasRevision, $totalVerified, $totalRevision);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $hasRevision 
                    ? 'Verifikasi berhasil disimpan. Terdapat dokumen yang perlu revisi.' 
                    : 'Verifikasi berhasil disimpan. Semua dokumen sesuai.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error saat submit verifikasi', [
                'permohonan_id' => $permohonan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tahapan verifikasi dan buat tahapan berikutnya jika selesai
     */
    private function updateTahapanVerifikasi($permohonan, $hasRevision, $totalVerified, $totalRevision)
    {
        $masterTahapan = MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
        
        if (!$masterTahapan) {
            return;
        }

        // Update tahapan Verifikasi
        PermohonanTahapan::updateOrCreate(
            [
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $masterTahapan->id,
            ],
            [
                'status' => $hasRevision ? 'revisi' : 'selesai',
                'catatan' => $hasRevision 
                    ? "Verifikasi selesai - {$totalRevision} dokumen perlu revisi, {$totalVerified} dokumen sesuai"
                    : "Verifikasi selesai - Semua dokumen ({$totalVerified}) telah diverifikasi dan sesuai",
                'updated_by' => auth()->id(),
            ]
        );

        // Jika tidak ada revisi, aktifkan tahapan berikutnya
        if (!$hasRevision) {
            $this->activateNextTahapan($permohonan, $masterTahapan);
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
                'catatan' => 'Tahapan dimulai setelah verifikasi selesai',
                'updated_by' => auth()->id(),
            ]
        );

        Log::info('Tahapan berikutnya diaktifkan', [
            'permohonan_id' => $permohonan->id,
            'tahapan' => $nextTahapan->nama_tahapan,
        ]);
    }

    /**
     * Log aktivitas verifikasi
     */
    private function logVerifikasi($permohonan, $verifications, $totalVerified, $totalRevision, $status)
    {
        activity()
            ->performedOn($permohonan)
            ->causedBy(auth()->user())
            ->withProperties([
                'total_dokumen' => count($verifications),
                'total_verified' => $totalVerified,
                'total_revision' => $totalRevision,
                'status' => $status,
                'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? null,
                'jenis_dokumen' => $permohonan->jenisDokumen->nama_dokumen ?? null,
                'tahun' => $permohonan->tahun,
            ])
            ->log('Verifikasi dokumen permohonan');

        Log::info('Verifikasi dokumen berhasil', [
            'permohonan_id' => $permohonan->id,
            'verifikator' => auth()->user()->name,
            'status' => $status,
            'total_verified' => $totalVerified,
            'total_revision' => $totalRevision,
        ]);
    }

    /**
     * Kirim notifikasi ke pemohon dan admin
     */
    private function sendNotifications($permohonan, $hasRevision, $totalVerified, $totalRevision)
    {
        // Notifikasi ke pemohon
        Notifikasi::create([
            'user_id' => $permohonan->user_id,
            'title' => $hasRevision ? 'Dokumen Perlu Revisi' : 'Verifikasi Dokumen Selesai',
            'message' => $hasRevision 
                ? "Terdapat {$totalRevision} dokumen yang perlu direvisi. Silakan periksa catatan verifikasi dan upload ulang dokumen yang diperlukan."
                : "Semua dokumen ({$totalVerified}) telah diverifikasi dan sesuai. Permohonan Anda akan dilanjutkan ke tahap berikutnya.",
            'type' => $hasRevision ? 'revision' : 'success',
            'action_url' => route('permohonan.tahapan.verifikasi', $permohonan),
            'notifiable_type' => Permohonan::class,
            'notifiable_id' => $permohonan->id,
        ]);

        // Notifikasi ke admin
        $admins = User::role(['admin_peran', 'kaban', 'superadmin'])->get();
        
        foreach ($admins as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'title' => 'Verifikasi Dokumen Selesai',
                'message' => sprintf(
                    'Verifikasi untuk %s - %s tahun %s telah selesai. Status: %s (%s dokumen sesuai, %s perlu revisi)',
                    $permohonan->kabupatenKota->nama ?? 'N/A',
                    $permohonan->jenisDokumen->nama_dokumen ?? 'N/A',
                    $permohonan->tahun,
                    $hasRevision ? 'Perlu Revisi' : 'Selesai',
                    $totalVerified,
                    $totalRevision
                ),
                'type' => $hasRevision ? 'warning' : 'info',
                'action_url' => route('permohonan.show', $permohonan),
                'notifiable_type' => Permohonan::class,
                'notifiable_id' => $permohonan->id,
            ]);
        }
    }
}
