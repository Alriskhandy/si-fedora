<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\SuratRekomendasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with([
            'kabupatenKota', 
            'jenisDokumen', 
            'suratRekomendasi'
        ])
        ->whereHas('suratRekomendasi', function($q) {
            $q->where('status', 'draft');
        });

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('jenisDokumen', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $permohonan = $query->latest()->paginate(10);

        return view('approval.index', compact('permohonan'));
    }

    public function show(Permohonan $permohonan)
    {
        // Load relasi surat rekomendasi
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'suratRekomendasi',
            'permohonanDokumen.masterKelengkapan'
        ]);

        // Pastikan ada surat rekomendasi dengan status draft
        if (!$permohonan->suratRekomendasi || $permohonan->suratRekomendasi->status !== 'draft') {
            abort(404, 'Draft rekomendasi tidak ditemukan.');
        }

        return view('approval.show', compact('permohonan'));
    }

    public function approve(Request $request, Permohonan $permohonan)
    {
        // Validasi
        $request->validate([
            'catatan_kaban' => 'nullable|string',
            'ttd_digital' => 'nullable|string', // Base64 dari signature pad
            'file_ttd' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120', // 5MB
        ]);

        // Get surat rekomendasi
        $suratRekomendasi = $permohonan->suratRekomendasi;
        if (!$suratRekomendasi) {
            return redirect()->back()->with('error', 'Surat rekomendasi tidak ditemukan.');
        }

        // Handle TTD
        $ttd_path = null;
        if ($request->hasFile('file_ttd')) {
            $ttd_path = $request->file('file_ttd')->store('ttd-kaban', 'public');
        } elseif ($request->ttd_digital) {
            // Simpan signature sebagai file PNG
            $data = $request->ttd_digital;
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded = base64_decode($data);
            $filename = 'ttd_kaban_' . $permohonan->id . '_' . time() . '.png';
            Storage::disk('public')->put('ttd-kaban/' . $filename, $decoded);
            $ttd_path = 'ttd-kaban/' . $filename;
        }

        // Update surat rekomendasi
        $suratRekomendasi->update([
            'status' => 'approved',
            'signed_by' => Auth::id(),
            'signed_at' => now(),
            'file_ttd' => $ttd_path,
        ]);

        return redirect()->route('approval.index')->with('success', 'Draft rekomendasi berhasil disetujui.');
    }

    public function reject(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'catatan_penolakan' => 'required|string|max:500',
        ]);

        // Get surat rekomendasi
        $suratRekomendasi = $permohonan->suratRekomendasi;
        if (!$suratRekomendasi) {
            return redirect()->back()->with('error', 'Surat rekomendasi tidak ditemukan.');
        }

        // Update status surat rekomendasi menjadi rejected atau kembali ke draft dengan catatan
        // Karena tidak ada status rejected di konstanta, kita tetap draft tapi bisa tambah field catatan
        // Atau bisa dihapus dan perlu dibuat ulang
        // Untuk sementara kita anggap ditolak = dihapus (soft delete)
        $suratRekomendasi->delete();

        return redirect()->route('approval.index')->with('success', 'Draft rekomendasi ditolak dan dihapus. Admin peran perlu membuat draft baru.');
    }

    public function downloadDraft(SuratRekomendasi $suratRekomendasi)
    {
        if (!$suratRekomendasi->file_surat) {
            abort(404, 'File draft tidak ditemukan.');
        }

        $filePath = storage_path('app/public/' . $suratRekomendasi->file_surat);
        
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        return response()->download($filePath);
    }
}