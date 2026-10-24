<?php

namespace App\Http\Controllers;

use App\Models\DokumenTahapan;
use App\Models\MasterTahapan;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use App\Services\PermohonanNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelaksanaanFasilitasiController extends Controller
{
    /**
     * Upload dokumen pelaksanaan
     */
    public function uploadDokumen(Request $request, Permohonan $permohonan)
    {
        if (! Auth::user()->can('pelaksanaan.upload-dokumen')) {
            abort(403, 'Anda tidak memiliki akses untuk upload dokumen.');
        }

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,pptx|max:102400', // 100MB per file
        ], [
            'files.required' => 'Pilih minimal 1 file untuk diupload.',
            'files.*.mimes' => 'Format file harus: PDF, JPG, JPEG, PNG, XLS, XLSX, atau PPTX.',
            'files.*.max' => 'Ukuran file maksimal 100MB.',
        ]);

        try {
            // Get master tahapan pelaksanaan
            $masterTahapan = MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->firstOrFail();

            $uploadedCount = 0;
            $uploadedFiles = [];

            // Loop through all uploaded files
            foreach ($request->file('files') as $file) {
                // Get original filename and remove extension for document name
                $originalName = $file->getClientOriginalName();
                $namaDokumen = pathinfo($originalName, PATHINFO_FILENAME);

                $fileName = $originalName;

                $filePath = $file->store('dokumen-tahapan/pelaksanaan/'.$permohonan->id, 'dokumen');

                // Create dokumen record
                $dokumen = DokumenTahapan::create([
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapan->id,
                    'user_id' => Auth::id(),
                    'nama_dokumen' => $namaDokumen,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                    'status' => 'menunggu',
                ]);

                $uploadedFiles[] = $namaDokumen;
                $uploadedCount++;
            }

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permohonan_id' => $permohonan->id,
                    'jumlah_file' => $uploadedCount,
                    'files' => $uploadedFiles,
                ])
                ->log('Upload '.$uploadedCount.' dokumen pelaksanaan');

            $message = $uploadedCount > 1
                ? $uploadedCount.' dokumen berhasil diupload.'
                : 'Dokumen berhasil diupload.';

            return redirect()->route('permohonan.tahapan.pelaksanaan', $permohonan)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error upload dokumen pelaksanaan: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat upload dokumen: '.$e->getMessage());
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

        if (! $dokumenTahapan->file_path || ! Storage::disk('dokumen')->exists($dokumenTahapan->file_path)) {
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
            ->log('Download dokumen: '.$dokumenTahapan->nama_dokumen);

        return Storage::disk('dokumen')->download(
            $dokumenTahapan->file_path,
            $dokumenTahapan->file_name
        );
    }

    /**
     * Delete dokumen tahapan
     */
    public function deleteDokumen(Permohonan $permohonan, $dokumen)
    {
        if (! Auth::user()->can('pelaksanaan.upload-dokumen')) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus dokumen.');
        }

        $dokumenTahapan = DokumenTahapan::findOrFail($dokumen);

        // Cek apakah dokumen milik permohonan ini
        if ($dokumenTahapan->permohonan_id != $permohonan->id) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        try {
            // Hapus file dari storage
            if ($dokumenTahapan->file_path && Storage::disk('dokumen')->exists($dokumenTahapan->file_path)) {
                Storage::disk('dokumen')->delete($dokumenTahapan->file_path);
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
                ->log('Hapus dokumen: '.$dokumenTahapan->nama_dokumen);

            // Delete record
            $dokumenTahapan->delete();

            return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error delete dokumen: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus dokumen.');
        }
    }

    /**
     * Selesaikan tahapan pelaksanaan dan lanjut ke tahapan berikutnya
     */
    public function completeTahapan(Permohonan $permohonan)
    {
        if (! Auth::user()->can('pelaksanaan.complete')) {
            abort(403, 'Anda tidak memiliki akses untuk menyelesaikan tahapan.');
        }

        try {
            // Update tahapan Pelaksanaan menjadi selesai
            $masterTahapanPelaksanaan = MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->first();
            if ($masterTahapanPelaksanaan) {
                PermohonanTahapan::updateOrCreate(
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

                PermohonanTahapan::updateOrCreate(
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

            // Kirim notifikasi melalui notification service (database + WhatsApp)
            $notificationService = app(PermohonanNotificationService::class);
            $notificationService->notifyPelaksanaanSelesai($permohonan);

            return redirect()->back()->with('success', 'Tahapan Pelaksanaan berhasil diselesaikan. Tahapan Hasil sudah dimulai.');

        } catch (\Exception $e) {
            Log::error('Error complete tahapan pelaksanaan: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyelesaikan tahapan.');
        }
    }

    /**
     * Helper method untuk cek akses permohonan
     */
    private function authorizeView(Permohonan $permohonan): void
    {
        Gate::authorize('view', $permohonan);
    }
}
