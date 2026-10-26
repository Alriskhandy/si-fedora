<?php

namespace App\Http\Controllers\Fasilitasi;

use App\Enums\StatusPermohonan;
use App\Http\Controllers\Controller;
use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanTahapan;
use App\Services\PermohonanNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Unggah dan hapus file dokumen kelengkapan permohonan oleh pemohon (tahapan 1 dan revisi).
 *
 * Akses: permohonan.upload-dokumen; hanya pemohon pembuat permohonan.
 */
class PermohonanDokumenController extends Controller
{
    /**
     * Unggah atau ganti file dokumen kelengkapan. Ditolak bila batas waktu unggah lewat (memperhitungkan perpanjangan), kecuali saat permohonan berstatus revisi.
     */
    public function upload(Request $request, PermohonanDokumen $permohonanDokumen)
    {
        Log::info('[Upload Dokumen] Mulai upload', [
            'dokumen_id' => $permohonanDokumen->id,
            'permohonan_id' => $permohonanDokumen->permohonan_id,
            'user_id' => Auth::id(),
            'has_file' => $request->hasFile('file'),
            'file_size' => $request->hasFile('file') ? $request->file('file')->getSize() : null,
            'content_length' => $request->header('Content-Length'),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size'),
        ]);

        $request->validate([
            'file' => 'required|file|mimes:pdf,xlsx,xls|max:102400', // 100MB
            'redirect_to' => 'nullable|in:permohonan,verifikasi',
        ], [
            'file.required' => 'File harus diupload',
            'file.mimes' => 'File harus berformat PDF atau Excel (xlsx, xls)',
            'file.max' => 'Ukuran file maksimal 100MB',
        ]);

        // Cek akses - hanya pemohon pemilik permohonan yang bisa upload
        if ($permohonanDokumen->permohonan->user_id !== Auth::id()) {
            Log::warning('[Upload Dokumen] Akses ditolak', [
                'dokumen_id' => $permohonanDokumen->id,
                'user_id' => Auth::id(),
                'owner_id' => $permohonanDokumen->permohonan->user_id,
            ]);

            return back()->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Cek status permohonan - hanya bisa upload jika status belum atau revisi
        $permohonan = $permohonanDokumen->permohonan()->with('jadwalFasilitasi')->first();
        if (! $permohonan->status_akhir->bisaUbahDokumen()) {
            Log::warning('[Upload Dokumen] Status tidak valid untuk upload', [
                'dokumen_id' => $permohonanDokumen->id,
                'status_akhir' => $permohonan->status_akhir,
            ]);

            return back()->with('error', 'Dokumen tidak dapat diupload. Permohonan sudah disubmit atau selesai.');
        }

        // Cek batas waktu - hanya berlaku untuk upload awal, bukan revisi
        if ($permohonan->status_akhir !== StatusPermohonan::Revisi && $permohonan->isUploadDeadlinePassed()) {
            Log::warning('[Upload Dokumen] Batas waktu terlewat', [
                'dokumen_id' => $permohonanDokumen->id,
                'batas' => $permohonan->getEffectiveDeadline(),
            ]);

            return back()->with('error', $permohonan->getUploadDeadlineMessage());
        }

        try {
            // Hapus file lama jika ada
            if ($permohonanDokumen->file_path) {
                Storage::disk('dokumen')->delete($permohonanDokumen->file_path);
                Log::info('[Upload Dokumen] File lama dihapus', ['old_path' => $permohonanDokumen->file_path]);
            }

            // Upload file baru
            $file = $request->file('file');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->store('permohonan_dokumen/'.$permohonanDokumen->permohonan_id, 'dokumen');

            if (! $filePath) {
                Log::error('[Upload Dokumen] Gagal menyimpan file ke storage', [
                    'dokumen_id' => $permohonanDokumen->id,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'disk_free' => disk_free_space(storage_path()),
                ]);

                return back()->with('error', 'Gagal menyimpan file. Silakan coba lagi.');
            }

            Log::info('[Upload Dokumen] File berhasil disimpan', [
                'dokumen_id' => $permohonanDokumen->id,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
            ]);

            // Update database
            $permohonanDokumen->update([
                'is_ada' => true,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'status_verifikasi' => 'pending',
            ]);

            $namaDokumen = $permohonanDokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen';

            // Update status permohonan jika ada dokumen yang diupload ulang dari status revisi
            if ($permohonan->status_akhir === StatusPermohonan::Revisi) {
                // Cek apakah masih ada dokumen dengan status 'revision'
                $masihAdaRevisi = $permohonan->permohonanDokumen()
                    ->where('status_verifikasi', 'revision')
                    ->where('id', '!=', $permohonanDokumen->id)
                    ->exists();

                // Jika sudah tidak ada dokumen revisi lagi, ubah status menjadi 'proses' untuk verifikasi ulang
                if (! $masihAdaRevisi) {
                    $permohonan->update(['status_akhir' => 'proses']);

                    // Update tahapan Verifikasi kembali ke status proses
                    $masterTahapanVerifikasi = MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
                    if ($masterTahapanVerifikasi) {
                        PermohonanTahapan::updateOrCreate(
                            [
                                'permohonan_id' => $permohonan->id,
                                'tahapan_id' => $masterTahapanVerifikasi->id,
                            ],
                            [
                                'status' => 'proses',
                                'catatan' => 'Dokumen revisi telah diupload ulang pada '.now()->format('d M Y H:i').'. Menunggu verifikasi ulang dari Tim Fedora.',
                                'updated_by' => Auth::id(),
                            ]
                        );
                    }

                    // Kirim notifikasi ke verifikator
                    $notificationService = app(PermohonanNotificationService::class);
                    $notificationService->notifyDokumenRevisiUploaded($permohonan, $namaDokumen);

                    Log::info('[Upload Dokumen] Semua revisi selesai, status diubah ke proses', [
                        'permohonan_id' => $permohonan->id,
                    ]);
                }
            }

            // Determine redirect target
            $redirectTo = $request->input('redirect_to', 'permohonan');
            $redirectRoute = $redirectTo === 'verifikasi'
                ? 'permohonan.tahapan.verifikasi'
                : 'permohonan.tahapan.permohonan';

            Log::info('[Upload Dokumen] Upload selesai', [
                'dokumen_id' => $permohonanDokumen->id,
                'nama_dokumen' => $namaDokumen,
            ]);

            return redirect()->route($redirectRoute, $permohonanDokumen->permohonan_id)
                ->with('success', 'Dokumen "'.$namaDokumen.'" berhasil diupload');
        } catch (\Exception $e) {
            Log::error('[Upload Dokumen] Exception', [
                'dokumen_id' => $permohonanDokumen->id,
                'permohonan_id' => $permohonanDokumen->permohonan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat upload: '.$e->getMessage());
        }
    }

    /**
     * Hapus file dokumen kelengkapan (baris dokumennya tetap).
     */
    public function removeFile(PermohonanDokumen $permohonanDokumen)
    {
        // Cek akses - hanya pemohon pemilik permohonan
        if ($permohonanDokumen->permohonan->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Cek status permohonan - hanya bisa hapus jika status belum atau revisi
        if (! $permohonanDokumen->permohonan->status_akhir->bisaUbahDokumen()) {
            return back()->with('error', 'Dokumen tidak dapat dihapus. Permohonan sudah disubmit atau selesai.');
        }

        if (! $permohonanDokumen->file_path) {
            return back()->with('error', 'Dokumen belum memiliki file untuk dihapus.');
        }

        // Hapus file dari storage
        Storage::disk('dokumen')->delete($permohonanDokumen->file_path);

        $namaDokumen = $permohonanDokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen';

        // Reset data dokumen, baris kelengkapan tetap ada agar bisa diupload ulang
        $permohonanDokumen->update([
            'is_ada' => false,
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'file_type' => null,
            'status_verifikasi' => 'pending',
            'catatan_verifikasi' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return redirect()->route('permohonan.tahapan.permohonan', $permohonanDokumen->permohonan_id)
            ->with('success', 'Dokumen "'.$namaDokumen.'" berhasil dihapus. Silakan upload ulang dokumen.');
    }
}
