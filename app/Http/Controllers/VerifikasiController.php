<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PersyaratanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifikasiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'permohonanDokumen.masterKelengkapan'])
            ->where('status_akhir', 'proses'); // Status proses = menunggu verifikasi

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

        $permohonan = $query->latest()->paginate(10);

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
            'dokumen.*.status_verifikasi' => 'required|in:verified,revision_required',
            'catatan_umum' => 'nullable|string',
            'status_verifikasi' => 'required|in:verified,revision_required',
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

            if ($data['status_verifikasi'] === 'revision_required') {
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
}
