<?php

namespace App\Http\Controllers;

use App\Models\PermohonanDokumen;
use App\Models\Permohonan;
use App\Models\PersyaratanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PermohonanDokumenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'permohonan_id' => 'required|exists:permohonan,id',
            'persyaratan_dokumen_id' => 'required|exists:persyaratan_dokumen,id|unique:permohonan_dokumen,permohonan_id,NULL,id,persyaratan_dokumen_id,' . $request->persyaratan_dokumen_id,
            'is_ada' => 'required|boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $permohonan = Permohonan::with('jadwalFasilitasi')->findOrFail($request->permohonan_id);

        // Cek akses
        if (Auth::user()->hasRole('pemohon')) {
            if ($permohonan->user_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }

        // Cek batas waktu upload (perpanjangan > jadwal)
        if ($permohonan->isUploadDeadlinePassed()) {
            return redirect()->back()->with('error', $permohonan->getUploadDeadlineMessage());
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->store('permohonan_dokumen/' . $permohonan->id, 'public');
        }

        PermohonanDokumen::create([
            'permohonan_id' => $request->permohonan_id,
            'persyaratan_dokumen_id' => $request->persyaratan_dokumen_id,
            'is_ada' => $request->is_ada,
            'file_path' => $filePath,
            'file_name' => $filePath ? $fileName : null,
            'file_size' => $filePath ? $file->getSize() : null,
            'file_type' => $filePath ? $file->getMimeType() : null,
        ]);

        return redirect()->route('permohonan.show', $permohonan)->with('success', 'Dokumen persyaratan berhasil ditambahkan.');
    }

    public function update(Request $request, PermohonanDokumen $permohonanDokumen)
    {
        $request->validate([
            'is_ada' => 'required|boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        // Cek akses
        if (Auth::user()->hasRole('pemohon')) {
            if ($permohonanDokumen->permohonan->user_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }

        // Cek batas waktu upload (perpanjangan > jadwal)
        $permohonan = $permohonanDokumen->permohonan()->with('jadwalFasilitasi')->first();
        if ($permohonan && $permohonan->isUploadDeadlinePassed()) {
            return redirect()->back()->with('error', $permohonan->getUploadDeadlineMessage());
        }

        $oldFilePath = $permohonanDokumen->file_path;

        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($oldFilePath) {
                Storage::disk('public')->delete($oldFilePath);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $newFilePath = $file->store('permohonan_dokumen/' . $permohonanDokumen->permohonan_id, 'public');

            $permohonanDokumen->update([
                'is_ada' => $request->is_ada,
                'file_path' => $newFilePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
            ]);
        } else {
            $permohonanDokumen->update([
                'is_ada' => $request->is_ada,
            ]);
        }

        return redirect()->route('permohonan.show', $permohonanDokumen->permohonan)->with('success', 'Dokumen persyaratan berhasil diperbarui.');
    }

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
            'file.max' => 'Ukuran file maksimal 100MB'
        ]);

        // Cek akses - hanya pemohon yang bisa upload
        if (Auth::user()->hasRole('pemohon')) {
            if ($permohonanDokumen->permohonan->user_id !== Auth::id()) {
                Log::warning('[Upload Dokumen] Akses ditolak', [
                    'dokumen_id' => $permohonanDokumen->id,
                    'user_id' => Auth::id(),
                    'owner_id' => $permohonanDokumen->permohonan->user_id,
                ]);
                return back()->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }

        // Cek status permohonan - hanya bisa upload jika status belum atau revisi
        $permohonan = $permohonanDokumen->permohonan()->with('jadwalFasilitasi')->first();
        if (!in_array($permohonan->status_akhir, ['belum', 'revisi'])) {
            Log::warning('[Upload Dokumen] Status tidak valid untuk upload', [
                'dokumen_id' => $permohonanDokumen->id,
                'status_akhir' => $permohonan->status_akhir,
            ]);
            return back()->with('error', 'Dokumen tidak dapat diupload. Permohonan sudah disubmit atau selesai.');
        }

        // Cek batas waktu - hanya berlaku untuk upload awal, bukan revisi
        if ($permohonan->status_akhir !== 'revisi' && $permohonan->isUploadDeadlinePassed()) {
            Log::warning('[Upload Dokumen] Batas waktu terlewat', [
                'dokumen_id' => $permohonanDokumen->id,
                'batas' => $permohonan->getEffectiveDeadline(),
            ]);
            return back()->with('error', $permohonan->getUploadDeadlineMessage());
        }

        try {
            // Hapus file lama jika ada
            if ($permohonanDokumen->file_path) {
                Storage::disk('public')->delete($permohonanDokumen->file_path);
                Log::info('[Upload Dokumen] File lama dihapus', ['old_path' => $permohonanDokumen->file_path]);
            }

            // Upload file baru
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->store('permohonan_dokumen/' . $permohonanDokumen->permohonan_id, 'public');

            if (!$filePath) {
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
            if ($permohonan->status_akhir === 'revisi') {
                // Cek apakah masih ada dokumen dengan status 'revision'
                $masihAdaRevisi = $permohonan->permohonanDokumen()
                    ->where('status_verifikasi', 'revision')
                    ->where('id', '!=', $permohonanDokumen->id)
                    ->exists();

                // Jika sudah tidak ada dokumen revisi lagi, ubah status menjadi 'proses' untuk verifikasi ulang
                if (!$masihAdaRevisi) {
                    $permohonan->update(['status_akhir' => 'proses']);

                    // Update tahapan Verifikasi kembali ke status proses
                    $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
                    if ($masterTahapanVerifikasi) {
                        \App\Models\PermohonanTahapan::updateOrCreate(
                            [
                                'permohonan_id' => $permohonan->id,
                                'tahapan_id' => $masterTahapanVerifikasi->id,
                            ],
                            [
                                'status' => 'proses',
                                'catatan' => 'Dokumen revisi telah diupload ulang pada ' . now()->format('d M Y H:i') . '. Menunggu verifikasi ulang dari Tim Fedora.',
                                'updated_by' => Auth::id(),
                            ]
                        );
                    }

                    // Kirim notifikasi ke verifikator
                    $notificationService = app(\App\Services\PermohonanNotificationService::class);
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
                ->with('success', 'Dokumen "' . $namaDokumen . '" berhasil diupload');
        } catch (\Exception $e) {
            Log::error('[Upload Dokumen] Exception', [
                'dokumen_id' => $permohonanDokumen->id,
                'permohonan_id' => $permohonanDokumen->permohonan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }

    public function removeFile(PermohonanDokumen $permohonanDokumen)
    {
        // Cek akses - hanya pemohon pemilik permohonan
        if (Auth::user()->hasRole('pemohon')) {
            if ($permohonanDokumen->permohonan->user_id !== Auth::id()) {
                return back()->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }

        // Cek status permohonan - hanya bisa hapus jika status belum atau revisi
        if (!in_array($permohonanDokumen->permohonan->status_akhir, ['belum', 'revisi'])) {
            return back()->with('error', 'Dokumen tidak dapat dihapus. Permohonan sudah disubmit atau selesai.');
        }

        if (!$permohonanDokumen->file_path) {
            return back()->with('error', 'Dokumen belum memiliki file untuk dihapus.');
        }

        // Hapus file dari storage
        Storage::disk('public')->delete($permohonanDokumen->file_path);

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
            ->with('success', 'Dokumen "' . $namaDokumen . '" berhasil dihapus. Silakan upload ulang dokumen.');
    }

    public function destroy(PermohonanDokumen $permohonanDokumen)
    {
        // Cek akses
        if (Auth::user()->hasRole('pemohon')) {
            if ($permohonanDokumen->permohonan->user_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }

        // Hapus file
        if ($permohonanDokumen->file_path) {
            Storage::disk('public')->delete($permohonanDokumen->file_path);
        }

        $permohonanDokumen->delete();

        return redirect()->route('permohonan.show', $permohonanDokumen->permohonan)->with('success', 'Dokumen persyaratan berhasil dihapus.');
    }

    public function download(PermohonanDokumen $permohonanDokumen)
    {
        $this->authorizeView($permohonanDokumen);

        if (!$permohonanDokumen->file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($permohonanDokumen->file_path, $permohonanDokumen->file_name);
    }

    private function authorizeView(PermohonanDokumen $permohonanDokumen)
    {
        $user = Auth::user();

        // Tambahin null check
        if (!$permohonanDokumen->permohonan) {
            abort(404, 'Permohonan tidak ditemukan.');
        }

        if ($user->hasRole('pemohon')) {
            if ($permohonanDokumen->permohonan->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }
        // Admin PERAN, Kaban, Superadmin bisa akses semua
        // Verifikator & Fasilitator cek via assignment (TODO: implement jika perlu)
    }
}
