<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Evaluasi;
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
            'evaluasi'
        ])
        ->where('status', 'draft_recommendation');

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
        // Pastikan statusnya draft_recommendation
        if ($permohonan->status !== 'draft_recommendation') {
            abort(404, 'Draft rekomendasi tidak ditemukan.');
        }

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'evaluasi',
            'permohonanDokumen.masterKelengkapan'
        ]);

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

        // Update evaluasi (jika ada)
        $evaluasi = Evaluasi::where('permohonan_id', $permohonan->id)->first();
        if ($evaluasi) {
            $evaluasi->update([
                'catatan_kaban' => $request->catatan_kaban,
                'approved_by_kaban' => Auth::id(),
                'approved_at' => now(),
            ]);
        }

        // Handle TTD
        $ttd_path = null;
        if ($request->hasFile('file_ttd')) {
            $ttd_path = $request->file('file_ttd')->store('ttd-kaban', 'public');
        } elseif ($request->ttd_digital) {
            // Simpan signature sebagai file PNG (opsional)
            $data = $request->ttd_digital;
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded = base64_decode($data);
            $filename = 'ttd_kaban_' . $permohonan->id . '_' . time() . '.png';
            Storage::disk('public')->put('ttd-kaban/' . $filename, $decoded);
            $ttd_path = 'ttd-kaban/' . $filename;
        }

        // Update permohonan
        $permohonan->update([
            'status' => 'approved_by_kaban',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'ttd_kaban' => $ttd_path,
        ]);

        return redirect()->route('approval.index')->with('success', 'Draft rekomendasi berhasil disetujui.');
    }

    public function reject(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'catatan_penolakan' => 'required|string|max:500',
        ]);

        $evaluasi = Evaluasi::where('permohonan_id', $permohonan->id)->first();
        if ($evaluasi) {
            $evaluasi->update([
                'catatan_kaban' => $request->catatan_penolakan,
                'rejected_by_kaban' => Auth::id(),
                'rejected_at' => now(),
            ]);
        }

        $permohonan->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
        ]);

        return redirect()->route('approval.index')->with('success', 'Draft rekomendasi ditolak.');
    }

    public function downloadDraft(Evaluasi $evaluasi)
    {
        if (!$evaluasi->file_draft) {
            abort(404, 'File draft tidak ditemukan.');
        }

        return Storage::disk('public')->download($evaluasi->file_draft);
    }
}