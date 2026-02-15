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

class PenetapanPerdaController extends Controller
{
    /**
     * Tampilkan daftar permohonan untuk penetapan PERDA (Pemohon)
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'tindakLanjut', 'penetapanPerda'])
            ->where('user_id', Auth::id())
            ->whereHas('tindakLanjut');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $permohonans = $query->latest()->paginate(10);

        return view('penetapan-perda.index', compact('permohonans'));
    }

    /**
     * Upload dokumen penetapan perda ke DokumenTahapan (belum submit)
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

            // Get tahapan Penetapan Perda (ID 7)
            $masterTahapan = MasterTahapan::where('id', 7)->first();
            
            if (!$masterTahapan) {
                return back()->with('error', 'Tahapan penetapan perda tidak ditemukan.');
            }

            // Upload file
            $file = $request->file('file');
            $fileName = 'penetapan_perda_' . $permohonan->id . '_' . time() . '.pdf';
            $filePath = $file->storeAs('dokumen-tahapan/penetapan-perda', $fileName, 'public');

            // Simpan ke DokumenTahapan
            $dokumen = DokumenTahapan::create([
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $masterTahapan->id,
                'user_id' => Auth::id(),
                'nama_dokumen' => 'Dokumen Penetapan PERDA/PERKADA',
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
                ->log('Dokumen penetapan perda diupload oleh ' . Auth::user()->name);

            DB::commit();

            return redirect()->back()->with('success', 'Dokumen penetapan perda berhasil diupload. Silakan preview dan submit dokumen.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading penetapan perda: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengupload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Submit dokumen penetapan perda (finalisasi)
     */
    public function submit(Request $request, Permohonan $permohonan)
    {
        // Pastikan permohonan milik user yang login
        if ($permohonan->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Pastikan sudah ada dokumen yang diupload
        $masterTahapan = MasterTahapan::where('id', 7)->first();
        
        if (!$masterTahapan) {
            return back()->with('error', 'Master tahapan penetapan perda tidak ditemukan.');
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

            // Update tahapan Penetapan Perda menjadi selesai
            PermohonanTahapan::updateOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapan->id,
                ],
                [
                    'status' => 'selesai',
                    'catatan' => 'Dokumen penetapan perda telah disubmit oleh pemohon',
                    'updated_by' => Auth::id(),
                ]
            );

            // Update status permohonan menjadi selesai
            $permohonan->update([
                'status_akhir' => 'selesai'
            ]);

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->log('Dokumen penetapan perda disubmit oleh ' . Auth::user()->name);

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
                    'title' => 'Dokumen Penetapan PERDA Disubmit',
                    'message' => 'Pemohon ' . Auth::user()->name . ' dari ' . ($permohonan->kabupatenKota->nama ?? '-') . ' telah submit dokumen penetapan perda untuk permohonan ' . $permohonan->nomor_permohonan,
                    'type' => 'penetapan_perda_submitted',
                    'model_type' => Permohonan::class,
                    'model_id' => $permohonan->id,
                    'action_url' => route('permohonan.tahapan.penetapan', $permohonan),
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Dokumen penetapan perda berhasil disubmit dan dapat dilihat oleh tim.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting penetapan perda: ' . $e->getMessage());
            return back()->with('error', 'Gagal submit dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download file penetapan
     */
    public function download(Permohonan $permohonan)
    {
        // Get dokumen from DokumenTahapan
        $masterTahapan = MasterTahapan::where('id', 7)->first();
        
        if (!$masterTahapan) {
            return back()->with('error', 'Tahapan penetapan perda tidak ditemukan.');
        }

        $dokumen = DokumenTahapan::where('permohonan_id', $permohonan->id)
            ->where('tahapan_id', $masterTahapan->id)
            ->latest()
            ->first();

        if (!$dokumen) {
            return back()->with('error', 'Dokumen penetapan tidak ditemukan.');
        }

        $filePath = $dokumen->file_path;

        if (!$filePath) {
            return back()->with('error', 'File penetapan tidak ditemukan.');
        }

        $filepath = storage_path('app/public/' . $filePath);

        if (!file_exists($filepath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        return response()->download($filepath);
    }
}
