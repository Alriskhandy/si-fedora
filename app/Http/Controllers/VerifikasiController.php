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
        $query = Permohonan::with(['kabupatenKota', 'jenisDokumen', 'permohonanDokumen.persyaratanDokumen'])
            ->whereIn('status', ['submitted', 'revision_required']);

        // Hanya verifikator yang ditugaskan
        if (Auth::user()->hasRole('verifikator')) {
            $query->where('verifikator_id', Auth::id());
        }

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('jenisDokumen', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $permohonan = $query->latest()->paginate(10);

        return view('verifikasi.index', compact('permohonan'));
    }

    // public function show(Permohonan $permohonan)
    // {
    //     // Cek akses
    //     if (Auth::user()->hasRole('verifikator')) {
    //         if ($permohonan->verifikator_id !== Auth::id()) {
    //             abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
    //         }
    //     }

    //     // Load dokumen lengkap
    //     $permohonan->load([
    //         'permohonanDokumen.persyaratanDokumen',
    //         'kabupatenKota',
    //         'jenisDokumen'
    //     ]);

    //     return view('verifikasi.show', compact('permohonan'));
    // }
    public function show(Permohonan $permohonan)
{
    // Cek akses
    if (Auth::user()->hasRole('verifikator')) {
        if ($permohonan->verifikator_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }
    }

    // Load data lengkap
    $permohonan->load([
        'kabupatenKota',
        'jenisDokumen',
        'permohonanDokumen.persyaratanDokumen' // <-- Tambahin ini
    ]);

    return view('verifikasi.show', compact('permohonan'));
}

    public function verifikasi(Request $request, Permohonan $permohonan)
    {
        // Cek akses
        if (Auth::user()->hasRole('verifikator')) {
            if ($permohonan->verifikator_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }

        // Validasi
        $request->validate([
            'dokumen' => 'required|array',
            'dokumen.*.is_ada' => 'required|boolean',
            'catatan_umum' => 'nullable|string',
            'status_verifikasi' => 'required|in:verified,revision_required',
        ]);

        // Update dokumen verifikasi
        foreach ($request->dokumen as $dokumenId => $data) {
            $dokumen = PermohonanDokumen::findOrFail($dokumenId);
            $dokumen->update([
                'is_ada' => $data['is_ada'],
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_verifikasi' => $data['catatan'] ?? null,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
        }

        // Update status permohonan
        $permohonan->update([
            'status' => $request->status_verifikasi,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        return redirect()->route('verifikasi.index')->with('success', 'Verifikasi berhasil disimpan.');
    }
}