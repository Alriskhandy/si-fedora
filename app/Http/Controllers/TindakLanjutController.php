<?php

namespace App\Http\Controllers;

use App\Models\DokumenTahapan;
use App\Models\MasterTahapan;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    /**
     * Download file laporan tindak lanjut
     */
    public function download(Permohonan $permohonan)
    {
        $tindakLanjut = $permohonan->tindakLanjut;

        if (!$tindakLanjut || !$tindakLanjut->file_laporan) {
            return back()->with('error', 'File laporan tidak ditemukan.');
        }

        $filepath = storage_path('app/public/' . $tindakLanjut->file_laporan);

        if (!file_exists($filepath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        return response()->download($filepath);
    }

    /**
     * Upload dokumen tindak lanjut ke DokumenTahapan (belum submit)
     */
    public function upload(Request $request, Permohonan $permohonan)
    {
        // Pastikan permohonan milik user yang login
        if ($permohonan->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:102400', // 100MB = 102400 KB
        ], [
            'file.required' => 'File dokumen wajib diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 100MB',
        ]);

        try {
            DB::beginTransaction();

            // Get tahapan Tindak Lanjut
            $masterTahapan = \App\Models\MasterTahapan::where('nama_tahapan', 'LIKE', '%Tindak Lanjut%')->first();
            
            if (!$masterTahapan) {
                return back()->with('error', 'Tahapan tindak lanjut tidak ditemukan.');
            }

            // Upload file
            $file = $request->file('file');
            $fileName = 'tindak_lanjut_' . $permohonan->id . '_' . time() . '.pdf';
            $filePath = $file->storeAs('dokumen-tahapan/tindak-lanjut', $fileName, 'public');

            // Simpan ke DokumenTahapan
            $dokumen = DokumenTahapan::create([
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $masterTahapan->id,
                'user_id' => Auth::id(),
                'nama_dokumen' => 'Dokumen Tindak Lanjut Hasil Fasilitasi',
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'status' => 'menunggu',
            ]);

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->withProperties([
                    'dokumen' => $fileName,
                    'ukuran' => $file->getSize(),
                ])
                ->log('Dokumen tindak lanjut diupload oleh ' . Auth::user()->name);

            DB::commit();

            return redirect()->back()->with('success', 'Dokumen tindak lanjut berhasil diupload. Silakan preview dan submit dokumen.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading tindak lanjut: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengupload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Submit dokumen tindak lanjut (finalisasi)
     */
    public function submit(Request $request, Permohonan $permohonan)
    {
        // Pastikan permohonan milik user yang login
        if ($permohonan->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Pastikan sudah ada dokumen yang diupload
        $masterTahapan = MasterTahapan::where('id', 6)->first();
        
        if (!$masterTahapan) {
            return back()->with('error', 'Master tahapan tindak lanjut tidak ditemukan.');
        }

        $dokumen = DokumenTahapan::where('permohonan_id', $permohonan->id)
            ->where('tahapan_id', $masterTahapan->id)
            ->latest()
            ->first();

        if (!$dokumen) {
            return back()->with('error', 'Belum ada dokumen yang diupload. Silakan upload dokumen terlebih dahulu.');
        }

        // Pastikan belum disubmit sebelumnya (cek apakah verified_by masih kosong)
        if ($dokumen->verified_by) {
            return back()->with('error', 'Dokumen sudah pernah disubmit sebelumnya.');
        }

        try {
            DB::beginTransaction();

            // Update DokumenTahapan dengan timestamp submit sebagai penanda
            $dokumen->update([
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // Update tahapan Tindak Lanjut menjadi selesai
            PermohonanTahapan::updateOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapan->id,
                ],
                [
                    'status' => 'selesai',
                    'catatan' => 'Dokumen tindak lanjut telah disubmit oleh pemohon',
                    'updated_by' => Auth::id(),
                ]
            );

            // Cari tahapan Penetapan Perda dan set menjadi proses
            $tahapanPenetapan = \App\Models\MasterTahapan::where('id', 7)
                ->first();

            if ($tahapanPenetapan) {
                PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $tahapanPenetapan->id,
                    ],
                    [
                        'status' => 'proses',
                        'catatan' => 'Tahapan penetapan perda dimulai setelah dokumen tindak lanjut disubmit',
                        'updated_by' => Auth::id(),
                    ]
                );
            }

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->log('Dokumen tindak lanjut disubmit oleh ' . Auth::user()->name);

            // Kirim notifikasi ke Superadmin, Admin Peran, Kaban, dan Tim yang di-assign
            // 1. Users dengan role tertentu
            $roleUsers = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['superadmin', 'admin_peran', 'kaban']);
            })->get();

            // 2. Tim fasilitator dan verifikator yang di-assign ke kabkota + jenis dokumen + tahun ini
            $timAssignments = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('jenis_dokumen_id', $permohonan->jenis_dokumen_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->with('user')
                ->get();

            $timUsers = $timAssignments->pluck('user')->filter();

            // Gabungkan dan hapus duplikat
            $targetUsers = $roleUsers->merge($timUsers)->unique('id');

            foreach ($targetUsers as $user) {
                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Dokumen Tindak Lanjut Disubmit',
                    'message' => 'Pemohon ' . Auth::user()->name . ' dari ' . ($permohonan->kabupatenKota->nama ?? '-') . ' telah submit dokumen tindak lanjut untuk permohonan ' . $permohonan->nomor_permohonan,
                    'type' => 'tindak_lanjut_submitted',
                    'model_type' => Permohonan::class,
                    'model_id' => $permohonan->id,
                    'action_url' => route('permohonan.tahapan.tindak-lanjut', $permohonan),
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Dokumen tindak lanjut berhasil disubmit dan dapat dilihat oleh tim.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting tindak lanjut: ' . $e->getMessage());
            return back()->with('error', 'Gagal submit dokumen: ' . $e->getMessage());
        }
    }
}
