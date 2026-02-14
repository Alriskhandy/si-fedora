<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\HasilFasilitasi;
use App\Models\PermohonanTahapan;
use App\Models\MasterTahapan;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk approval draft final hasil fasilitasi oleh Kepala Badan
 * 
 * Fitur utama:
 * - List draft final yang menunggu persetujuan
 * - Review detail draft final
 * - Approve draft final (update tahapan ke selesai dan lanjut ke tahapan berikutnya)
 * - Reject draft final dengan catatan revisi (admin/koordinator perlu upload ulang)
 * - Download draft final PDF
 * - Activity logging dan notifications ke pemohon, admin, dan tim
 */
class ApprovalController extends Controller
{
    // ============================================================
    // AUTHORIZATION HELPER METHODS
    // ============================================================

    /**
     * Check if user is Kepala Badan
     * 
     * @return bool
     */
    private function isKepalaBadan(): bool
    {
        return Auth::user()->hasRole('kaban');
    }

    /**
     * Check if user is admin or superadmin
     * 
     * @return bool
     */
    private function isAdmin(): bool
    {
        return Auth::user()->hasAnyRole(['admin_peran', 'superadmin']);
    }

    // ============================================================
    // NOTIFICATION HELPER METHODS
    // ============================================================

    /**
     * Kirim notifikasi ke admin
     * 
     * @param Permohonan $permohonan
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    private function notifyAdmins(Permohonan $permohonan, string $title, string $message, string $type = 'info'): void
    {
        $admins = User::role(['admin_peran', 'superadmin'])->get();

        Log::info('Notifying Admins', [
            'admin_count' => $admins->count(),
            'permohonan_id' => $permohonan->id,
        ]);

        foreach ($admins as $admin) {
            if ($admin->id != Auth::id()) {
                try {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'model_type' => HasilFasilitasi::class,
                        'model_id' => $permohonan->hasilFasilitasi?->id,
                        'action_url' => route('hasil-fasilitasi.show', $permohonan->id),
                        'is_read' => false,
                    ]);
                    Log::info('Notification created for admin', ['user_id' => $admin->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification for admin', [
                        'user_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Kirim notifikasi ke tim fasilitasi
     * 
     * @param Permohonan $permohonan
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    private function notifyTeamMembers(Permohonan $permohonan, string $title, string $message, string $type = 'info'): void
    {
        $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('jenis_dokumen_id', $permohonan->jenis_dokumen_id)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->whereIn('role_type', ['fasilitator', 'verifikator'])
            ->get();

        Log::info('Notifying Team Members', [
            'team_count' => $assignments->count(),
            'permohonan_id' => $permohonan->id,
        ]);

        foreach ($assignments as $assignment) {
            if ($assignment->user_id != Auth::id()) {
                try {
                    Notifikasi::create([
                        'user_id' => $assignment->user_id,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'model_type' => HasilFasilitasi::class,
                        'model_id' => $permohonan->hasilFasilitasi?->id,
                        'action_url' => route('hasil-fasilitasi.show', $permohonan->id),
                        'is_read' => false,
                    ]);
                    Log::info('Notification created for team member', ['user_id' => $assignment->user_id]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification for team member', [
                        'user_id' => $assignment->user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Kirim notifikasi ke pemohon
     * 
     * @param Permohonan $permohonan
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    private function notifyPemohon(Permohonan $permohonan, string $title, string $message, string $type = 'info'): void
    {
        if ($permohonan->user_id && $permohonan->user_id != Auth::id()) {
            try {
                Notifikasi::create([
                    'user_id' => $permohonan->user_id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'model_type' => HasilFasilitasi::class,
                    'model_id' => $permohonan->hasilFasilitasi?->id,
                    'action_url' => route('permohonan.show', $permohonan->id),
                    'is_read' => false,
                ]);
                Log::info('Notification created for pemohon', ['user_id' => $permohonan->user_id]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for pemohon', [
                    'user_id' => $permohonan->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::info('Pemohon notification skipped', [
                'permohonan_user_id' => $permohonan->user_id,
                'current_user_id' => Auth::id(),
            ]);
        }
    }

    // ============================================================
    // MAIN APPROVAL OPERATIONS
    // ============================================================

    /**
     * Tampilkan daftar draft final hasil fasilitasi yang menunggu persetujuan
     * Hanya dapat diakses oleh Kepala Badan dan Admin
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    /**
     * Tampilkan daftar draft final hasil fasilitasi yang menunggu persetujuan
     * Hanya dapat diakses oleh Kepala Badan dan Admin
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Check authorization
        if (!$this->isKepalaBadan() && !$this->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }

        $query = Permohonan::with([
            'kabupatenKota', 
            'jenisDokumen', 
            'hasilFasilitasi'
        ])
        ->whereHas('hasilFasilitasi', function($q) {
            $q->where('status_draft', 'menunggu_persetujuan_kaban')
              ->whereNotNull('draft_final_file');
        });

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('kabupatenKota', function($subQ) use ($request) {
                    $subQ->where('nama', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('jenisDokumen', function($subQ) use ($request) {
                    $subQ->where('nama', 'like', '%' . $request->search . '%');
                });
            });
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $permohonan = $query->latest('updated_at')->paginate(10);
        
        // Debug log
        Log::info('Approval Index', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->roles->pluck('name'),
            'permohonan_count' => $permohonan->total(),
        ]);

        return view('pages.approval.index', compact('permohonan'));
    }

    /**
     * Tampilkan detail draft final hasil fasilitasi untuk review
     * Hanya dapat diakses oleh Kepala Badan dan Admin
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\View\View
     */
    public function show(Permohonan $permohonan)
    {
        // Check authorization
        if (!$this->isKepalaBadan() && !$this->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }

        // Load relasi hasil fasilitasi
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'hasilFasilitasi.hasilSistematika.masterBab',
            'hasilFasilitasi.hasilUrusan.masterUrusan',
            'hasilFasilitasi.pembuat'
        ]);

        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        // Debug log
        Log::info('Approval Show', [
            'user_id' => Auth::id(),
            'permohonan_id' => $permohonan->id,
            'hasilFasilitasi_exists' => $hasilFasilitasi ? 'yes' : 'no',
            'status_draft' => $hasilFasilitasi?->status_draft ?? 'null',
            'draft_final_file' => $hasilFasilitasi?->draft_final_file ?? 'null',
        ]);

        // Pastikan ada hasil fasilitasi
        if (!$hasilFasilitasi) {
            return redirect()->route('approval.index')
                ->with('error', 'Hasil fasilitasi tidak ditemukan untuk permohonan ini.');
        }

        // Pastikan ada draft final file
        if (!$hasilFasilitasi->draft_final_file) {
            return redirect()->route('approval.index')
                ->with('error', 'Draft final belum diupload. Silakan hubungi admin untuk mengupload draft final terlebih dahulu.');
        }

        // Pastikan status sudah menunggu persetujuan
        if ($hasilFasilitasi->status_draft !== 'menunggu_persetujuan_kaban') {
            $statusMessage = match($hasilFasilitasi->status_draft) {
                'disetujui_kaban' => 'Draft final sudah disetujui.',
                'ditolak_kaban' => 'Draft final ditolak dan memerlukan revisi.',
                null, '' => 'Draft final belum diajukan untuk persetujuan. Silakan hubungi admin untuk submit ke Kepala Badan.',
                default => 'Status draft tidak valid untuk review persetujuan.'
            };
            
            return redirect()->route('approval.index')
                ->with('info', $statusMessage);
        }

        // Sort sistematika by bab urutan
        $sortedSistematika = $hasilFasilitasi->hasilSistematika->sortBy(function ($item) {
            return $item->masterBab->urutan ?? 999;
        });
        $hasilFasilitasi->setRelation('hasilSistematika', $sortedSistematika);

        // Sort urusan by master urusan urutan
        $sortedUrusan = $hasilFasilitasi->hasilUrusan->sortBy(function ($item) {
            return $item->masterUrusan->urutan ?? 999;
        });
        $hasilFasilitasi->setRelation('hasilUrusan', $sortedUrusan);

        // Get tim info
        $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('jenis_dokumen_id', $permohonan->jenis_dokumen_id)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->with('user')
            ->get();

        $timInfo = [
            'verifikator' => $assignments->where('role_type', 'verifikator')->where('is_pic', true)->first(),
            'koordinator' => $assignments->where('role_type', 'fasilitator')->where('is_pic', true)->first(),
            'anggota' => $assignments->where('role_type', 'fasilitator')->where('is_pic', false)->values()
        ];

        return view('pages.approval.show', compact('permohonan', 'hasilFasilitasi', 'timInfo'));
    }

    /**
     * Approve draft final hasil fasilitasi
     * Hanya dapat dilakukan oleh Kepala Badan
     * Mengupdate tahapan hasil fasilitasi ke selesai dan tahapan berikutnya ke proses
     * 
     * @param Request $request
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, Permohonan $permohonan)
    {
        // Only Kepala Badan can approve
        if (!$this->isKepalaBadan()) {
            return redirect()->back()->with('error', 'Hanya Kepala Badan yang dapat menyetujui dokumen.');
        }

        // Validasi
        $request->validate([
            'keterangan_kaban' => 'nullable|string|max:1000',
        ]);

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi tidak ditemukan.');
            }

            // Check if status is correct
            if ($hasilFasilitasi->status_draft !== 'menunggu_persetujuan_kaban') {
                return redirect()->back()->with('error', 'Dokumen tidak dalam status menunggu persetujuan.');
            }

            // Update hasil fasilitasi
            $hasilFasilitasi->update([
                'status_draft' => 'disetujui_kaban',
                'tanggal_disetujui_kaban' => now(),
                'keterangan_kaban' => $request->keterangan_kaban,
                'updated_by' => Auth::id(),
            ]);

            // Log untuk debugging
            Log::info('Approval Process Started', [
                'permohonan_id' => $permohonan->id,
                'hasil_fasilitasi_updated' => true,
            ]);

            // Update tahapan hasil fasilitasi ke selesai
            $masterHasilFasilitasi = MasterTahapan::where('nama_tahapan', 'LIKE', '%Hasil Fasilitasi%')
                ->orWhere('nama_tahapan', 'LIKE', '%Evaluasi%')
                ->first();

            Log::info('Master Tahapan Hasil Fasilitasi Query', [
                'found' => $masterHasilFasilitasi ? 'yes' : 'no',
                'tahapan_id' => $masterHasilFasilitasi?->id,
                'tahapan_nama' => $masterHasilFasilitasi?->nama_tahapan,
            ]);

            if ($masterHasilFasilitasi) {
                PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterHasilFasilitasi->id,
                    ],
                    [
                        'status' => 'selesai',
                        'tanggal_selesai' => Carbon::now(),
                        'catatan' => 'Disetujui oleh Kepala Badan: ' . ($request->keterangan_kaban ?? 'Tidak ada catatan'),
                        'updated_by' => Auth::id(),
                    ]
                );

                Log::info('Tahapan Hasil Fasilitasi Updated', [
                    'tahapan_id' => $masterHasilFasilitasi->id,
                    'status_updated' => 'selesai',
                ]);

                // Update tahapan berikutnya ke proses
                $masterTahapanBerikutnya = MasterTahapan::where('urutan', '>', $masterHasilFasilitasi->urutan)
                    ->orderBy('urutan')
                    ->first();

                Log::info('Master Tahapan Berikutnya Query', [
                    'found' => $masterTahapanBerikutnya ? 'yes' : 'no',
                    'tahapan_id' => $masterTahapanBerikutnya?->id,
                    'tahapan_nama' => $masterTahapanBerikutnya?->nama_tahapan,
                ]);

                if ($masterTahapanBerikutnya) {
                    PermohonanTahapan::updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $masterTahapanBerikutnya->id,
                        ],
                        [
                            'status' => 'proses',
                            'tanggal_mulai' => Carbon::now(),
                            'catatan' => 'Tahapan dimulai setelah hasil fasilitasi disetujui',
                            'updated_by' => Auth::id(),
                        ]
                    );

                    Log::info('Tahapan Berikutnya Updated', [
                        'tahapan_id' => $masterTahapanBerikutnya->id,
                        'tahapan_nama' => $masterTahapanBerikutnya->nama_tahapan,
                        'status_updated' => 'proses',
                    ]);
                }
            }

            // Activity Log
            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('approved_by_kaban')
                ->withProperties([
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                    'status' => 'disetujui_kaban',
                    'keterangan' => $request->keterangan_kaban,
                ])
                ->log('Draft final hasil fasilitasi disetujui oleh Kepala Badan');

            Log::info('Activity Log Created');

            // Notifikasi ke pemohon
            try {
                $this->notifyPemohon(
                    $permohonan,
                    'Hasil Fasilitasi Disetujui',
                    'Hasil fasilitasi untuk permohonan ' . $permohonan->kabupatenKota->nama . ' telah disetujui oleh Kepala Badan.',
                    'success'
                );
                Log::info('Notification to Pemohon sent');
            } catch (\Exception $e) {
                Log::error('Failed to notify Pemohon: ' . $e->getMessage());
            }

            // Notifikasi ke admin
            try {
                $this->notifyAdmins(
                    $permohonan,
                    'Draft Final Disetujui',
                    'Draft final hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' telah disetujui oleh Kepala Badan.',
                    'success'
                );
                Log::info('Notification to Admins sent');
            } catch (\Exception $e) {
                Log::error('Failed to notify Admins: ' . $e->getMessage());
            }

            // Notifikasi ke tim
            try {
                $this->notifyTeamMembers(
                    $permohonan,
                    'Draft Final Disetujui',
                    'Draft final hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' telah disetujui oleh Kepala Badan.',
                    'success'
                );
                Log::info('Notification to Team Members sent');
            } catch (\Exception $e) {
                Log::error('Failed to notify Team Members: ' . $e->getMessage());
            }

            Log::info('Approval Process Completed Successfully');

            return redirect()->route('approval.index')->with('success', 'Draft final hasil fasilitasi berhasil disetujui. Tahapan berikutnya telah dimulai.');
        } catch (\Exception $e) {
            Log::error('Error approving draft final: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Reject/Request revision untuk draft final hasil fasilitasi
     * Hanya dapat dilakukan oleh Kepala Badan
     * Admin/koordinator harus upload ulang dan submit ulang
     * 
     * @param Request $request
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, Permohonan $permohonan)
    {
        // Only Kepala Badan can reject
        if (!$this->isKepalaBadan()) {
            return redirect()->back()->with('error', 'Hanya Kepala Badan yang dapat meminta revisi dokumen.');
        }

        $request->validate([
            'catatan_penolakan' => 'required|string|max:1000',
        ]);

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi tidak ditemukan.');
            }

            // Check if status is correct
            if ($hasilFasilitasi->status_draft !== 'menunggu_persetujuan_kaban') {
                return redirect()->back()->with('error', 'Dokumen tidak dalam status menunggu persetujuan.');
            }

            // Update status kembali ke ditolak dengan catatan revisi
            $hasilFasilitasi->update([
                'status_draft' => 'ditolak_kaban',
                'keterangan_kaban' => $request->catatan_penolakan,
                'tanggal_disetujui_kaban' => null,
                'updated_by' => Auth::id(),
            ]);

            Log::info('Rejection Process', [
                'permohonan_id' => $permohonan->id,
                'hasil_fasilitasi_updated' => true,
                'status' => 'ditolak_kaban',
            ]);

            // Activity Log
            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('rejected_by_kaban')
                ->withProperties([
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                    'status' => 'ditolak_kaban',
                    'keterangan' => $request->catatan_penolakan,
                ])
                ->log('Draft final hasil fasilitasi ditolak oleh Kepala Badan dan memerlukan revisi');

            // Notifikasi ke admin
            try {
                $this->notifyAdmins(
                    $permohonan,
                    'Draft Final Memerlukan Revisi',
                    'Draft final hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' memerlukan revisi. Catatan: ' . $request->catatan_penolakan,
                    'warning'
                );
                Log::info('Notification to Admins sent (rejection)');
            } catch (\Exception $e) {
                Log::error('Failed to notify Admins (rejection): ' . $e->getMessage());
            }

            // Notifikasi ke tim
            try {
                $this->notifyTeamMembers(
                    $permohonan,
                    'Draft Final Memerlukan Revisi',
                    'Draft final hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' memerlukan revisi dari Kepala Badan. Silakan perbaiki dan upload ulang.',
                    'warning'
                );
                Log::info('Notification to Team Members sent (rejection)');
            } catch (\Exception $e) {
                Log::error('Failed to notify Team Members (rejection): ' . $e->getMessage());
            }

            Log::info('Rejection Process Completed Successfully');

            return redirect()->route('approval.index')->with('success', 'Draft final memerlukan revisi. Admin dan tim akan menerima notifikasi untuk melakukan perbaikan dan upload ulang.');
        } catch (\Exception $e) {
            Log::error('Error rejecting draft final: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses permintaan revisi: ' . $e->getMessage());
        }
    }

    // ============================================================
    // DOCUMENT DOWNLOAD OPERATIONS
    // ============================================================

    /**
     * Download draft final PDF hasil fasilitasi
     * Dapat diakses oleh Kepala Badan dan Admin
     * 
     * @param Permohonan $permohonan
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadDraftFinal(Permohonan $permohonan)
    {
        // Check authorization
        if (!$this->isKepalaBadan() && !$this->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi tidak ditemukan.');
            }

            // Check if draft final exists
            if (!$hasilFasilitasi->draft_final_file || !Storage::disk('public')->exists($hasilFasilitasi->draft_final_file)) {
                return redirect()->back()->with('error', 'Draft final PDF tidak tersedia.');
            }

            $filename = 'Draft_Final_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.pdf';
            return response()->download(
                Storage::disk('public')->path($hasilFasilitasi->draft_final_file),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error downloading draft final: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh draft final: ' . $e->getMessage());
        }
    }
}