<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\DokumenTahapan;
use App\Models\MasterTahapan;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelaksanaanFasilitasiController extends Controller
{
    /**
     * Upload dokumen pelaksanaan
     */
    public function uploadDokumen(Request $request, Permohonan $permohonan)
    {
        // Cek akses - hanya fasilitator, koordinator, verifikator, admin yang bisa upload
        if (!Auth::user()->hasAnyRole(['fasilitator', 'koordinator', 'verifikator', 'admin_peran', 'superadmin'])) {
            abort(403, 'Anda tidak memiliki akses untuk upload dokumen.');
        }

        $request->validate([
            'jenis_dokumen' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,pptx|max:10240', // 10MB
        ]);

        try {
            // Get master tahapan pelaksanaan
            $masterTahapan = MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->firstOrFail();

            // Upload file
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs(
                'dokumen-tahapan/pelaksanaan/' . $permohonan->id,
                $fileName,
                'public'
            );

            // Create dokumen record
            $dokumen = DokumenTahapan::create([
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $masterTahapan->id,
                'user_id' => Auth::id(),
                'nama_dokumen' => $request->jenis_dokumen,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'status' => 'menunggu',
            ]);

            // Log activity
            activity()
                ->performedOn($dokumen)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permohonan_id' => $permohonan->id,
                    'jenis_dokumen' => $request->jenis_dokumen,
                    'file_name' => $fileName,
                ])
                ->log('Upload dokumen pelaksanaan: ' . $request->jenis_dokumen);

            return redirect()->route('permohonan.tahapan.pelaksanaan', $permohonan)
                ->with('success', 'Dokumen berhasil diupload.');

        } catch (\Exception $e) {
            Log::error('Error upload dokumen pelaksanaan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen tahapan
     */
    public function downloadDokumen(Permohonan $permohonan, $dokumen)
    {
        $dokumenTahapan = DokumenTahapan::findOrFail($dokumen);

        // Cek apakah dokumen milik permohonan ini
        if ($dokumenTahapan->permohonan_id != $permohonan->id) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        // Cek akses menggunakan helper
        $this->authorizeView($permohonan);

        if (!$dokumenTahapan->file_path || !Storage::disk('public')->exists($dokumenTahapan->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Log activity
        activity()
            ->performedOn($dokumenTahapan)
            ->causedBy(Auth::user())
            ->withProperties([
                'permohonan_id' => $permohonan->id,
                'file_name' => $dokumenTahapan->file_name,
            ])
            ->log('Download dokumen: ' . $dokumenTahapan->nama_dokumen);

        return Storage::disk('public')->download(
            $dokumenTahapan->file_path,
            $dokumenTahapan->file_name
        );
    }

    /**
     * Delete dokumen tahapan
     */
    public function deleteDokumen(Permohonan $permohonan, $dokumen)
    {
        // Cek akses - hanya fasilitator, koordinator, verifikator, admin yang bisa delete
        if (!Auth::user()->hasAnyRole(['fasilitator', 'koordinator', 'verifikator', 'admin_peran', 'superadmin'])) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus dokumen.');
        }

        $dokumenTahapan = DokumenTahapan::findOrFail($dokumen);

        // Cek apakah dokumen milik permohonan ini
        if ($dokumenTahapan->permohonan_id != $permohonan->id) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        try {
            // Hapus file dari storage
            if ($dokumenTahapan->file_path && Storage::disk('public')->exists($dokumenTahapan->file_path)) {
                Storage::disk('public')->delete($dokumenTahapan->file_path);
            }

            // Log before delete
            activity()
                ->performedOn($dokumenTahapan)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permohonan_id' => $permohonan->id,
                    'file_name' => $dokumenTahapan->file_name,
                    'jenis_dokumen' => $dokumenTahapan->nama_dokumen,
                ])
                ->log('Hapus dokumen: ' . $dokumenTahapan->nama_dokumen);

            // Delete record
            $dokumenTahapan->delete();

            return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error delete dokumen: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus dokumen.');
        }
    }

    /**
     * Selesaikan tahapan pelaksanaan dan lanjut ke tahapan berikutnya
     */
    public function completeTahapan(Permohonan $permohonan)
    {
        // Cek akses - hanya admin yang bisa complete tahapan
        if (!Auth::user()->hasAnyRole(['admin_peran', 'superadmin'])) {
            abort(403, 'Anda tidak memiliki akses untuk menyelesaikan tahapan.');
        }

        try {
            // Update tahapan Pelaksanaan menjadi selesai
            $masterTahapanPelaksanaan = MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->first();
            if ($masterTahapanPelaksanaan) {
                \App\Models\PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanPelaksanaan->id,
                    ],
                    [
                        'status' => 'selesai',
                        'tanggal_selesai' => Carbon::now(),
                        'catatan' => 'Pelaksanaan fasilitasi telah selesai dilaksanakan',
                        'updated_by' => Auth::id(),
                    ]
                );

                Log::info('Tahapan Pelaksanaan diselesaikan', [
                    'permohonan_id' => $permohonan->id,
                    'tahapan' => 'Pelaksanaan',
                    'user_id' => Auth::id(),
                ]);
            }

            // Buat/update tahapan berikutnya (Hasil) menjadi proses
            $masterTahapanHasil = MasterTahapan::where('nama_tahapan', 'Hasil Fasilitasi / Evaluasi')->first();
            if ($masterTahapanHasil) {
                // Set deadline 3 hari setelah pelaksanaan selesai sampai jam 24:00
                $deadline = Carbon::now()->addDays(3)->endOfDay();
                
                \App\Models\PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanHasil->id,
                    ],
                    [
                        'status' => 'proses',
                        'tanggal_mulai' => Carbon::now(),
                        'deadline' => $deadline,
                        'catatan' => 'Menunggu input hasil fasilitasi dari Fasilitator',
                        'updated_by' => Auth::id(),
                    ]
                );

                Log::info('Tahapan Hasil dimulai dengan deadline', [
                    'permohonan_id' => $permohonan->id,
                    'tahapan' => 'Hasil Fasilitasi / Evaluasi',
                    'deadline' => $deadline->format('Y-m-d H:i:s'),
                    'user_id' => Auth::id(),
                ]);
            }

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permohonan_id' => $permohonan->id,
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? '-',
                ])
                ->log('Tahapan Pelaksanaan diselesaikan, lanjut ke tahapan Hasil');

            // Kirim notifikasi ke pemohon
            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => 'Pelaksanaan Fasilitasi Selesai',
                'message' => sprintf(
                    'Tahapan Pelaksanaan Fasilitasi untuk %s telah diselesaikan. Sistem akan melanjutkan ke tahapan Hasil Fasilitasi.',
                    $permohonan->jenisDokumen->nama_dokumen ?? 'permohonan Anda'
                ),
                'type' => 'success',
                'action_url' => route('permohonan.tahapan.pelaksanaan', $permohonan),
                'notifiable_type' => Permohonan::class,
                'notifiable_id' => $permohonan->id,
            ]);

            // Kirim notifikasi ke tim fasilitasi yang di-assign
            $timFasilitasi = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->with('user')
                ->get();

            foreach ($timFasilitasi as $assignment) {
                if ($assignment->user && $assignment->user->hasAnyRole(['fasilitator', 'koordinator'])) {
                    Notifikasi::create([
                        'user_id' => $assignment->user_id,
                        'title' => 'Input Hasil Fasilitasi Diperlukan',
                        'message' => sprintf(
                            'Pelaksanaan fasilitasi untuk %s - %s telah selesai. Silakan input hasil fasilitasi pada tahapan berikutnya.',
                            $permohonan->kabupatenKota->nama ?? 'N/A',
                            $permohonan->jenisDokumen->nama_dokumen ?? 'N/A'
                        ),
                        'type' => 'info',
                        'action_url' => route('permohonan.tahapan.hasil', $permohonan),
                        'notifiable_type' => Permohonan::class,
                        'notifiable_id' => $permohonan->id,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Tahapan Pelaksanaan berhasil diselesaikan. Tahapan Hasil sudah dimulai.');

        } catch (\Exception $e) {
            Log::error('Error complete tahapan pelaksanaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyelesaikan tahapan.');
        }
    }

    /**
     * Helper method untuk cek akses permohonan
     */
    private function authorizeView(Permohonan $permohonan)
    {
        $user = Auth::user();

        // Pemohon (Kabupaten/Kota) hanya bisa lihat permohonan miliknya sendiri
        if ($user->hasRole('pemohon')) {
            if ($permohonan->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Verifikator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('verifikator')) {
            $hasAccess = \App\Models\UserKabkotaAssignment::where('user_id', $user->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Fasilitator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('fasilitator')) {
            $hasAccess = \App\Models\UserKabkotaAssignment::where('user_id', $user->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Koordinator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('koordinator')) {
            $hasAccess = \App\Models\UserKabkotaAssignment::where('user_id', $user->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Admin, Kaban, Superadmin, dan Auditor bisa lihat semua permohonan
        elseif ($user->hasAnyRole(['admin_peran', 'kaban', 'superadmin', 'auditor'])) {
            // Full access - no restriction
            return;
        }
        // Role lain tidak memiliki akses
        else {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }
    }
}
