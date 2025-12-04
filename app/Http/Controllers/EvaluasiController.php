<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Evaluasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvaluasiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jenisDokumen', 'evaluasi'])
            ->where('status', 'in_evaluation');

        // Hanya tim pokja yang ditugaskan
        if (Auth::user()->hasRole('pokja')) {
            $query->where('pokja_id', Auth::id());
        }

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('jenisDokumen', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $permohonan = $query->latest()->paginate(10);

        return view('evaluasi.index', compact('permohonan'));
    }

    public function show(Permohonan $permohonan)
    {
        // Cek akses
        if (Auth::user()->hasRole('pokja')) {
            if ($permohonan->pokja_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }

        // Load data lengkap
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'permohonanDokumen.masterKelengkapan',
            'evaluasi'
        ]);

        // Cek apakah evaluasi sudah ada
        $evaluasi = $permohonan->evaluasi()->first();

        return view('evaluasi.show', compact('permohonan', 'evaluasi'));
    }

    public function store(Request $request, Permohonan $permohonan)
    {
        // Cek akses
        if (Auth::user()->hasRole('pokja')) {
            if ($permohonan->pokja_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }

        // Validasi
        $request->validate([
            'draft_rekomendasi' => 'required|string',
            'file_draft' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB
            'catatan_evaluasi' => 'nullable|string',
        ]);

        $file_path = null;
        if ($request->hasFile('file_draft')) {
            $file_path = $request->file('file_draft')->store('evaluasi-draft', 'public');
        }

        // Cek apakah evaluasi sudah ada
        $evaluasi = Evaluasi::where('permohonan_id', $permohonan->id)->first();

        if ($evaluasi) {
            // Update evaluasi yang sudah ada
            $evaluasi->update([
                'draft_rekomendasi' => $request->draft_rekomendasi,
                'file_draft' => $file_path ?? $evaluasi->file_draft,
                'catatan_evaluasi' => $request->catatan_evaluasi,
                'evaluated_by' => Auth::id(),
                'evaluated_at' => now(),
            ]);
        } else {
            // Buat evaluasi baru
            Evaluasi::create([
                'permohonan_id' => $permohonan->id,
                'tahun_anggaran_id' => $permohonan->tahun_anggaran_id,
                'draft_rekomendasi' => $request->draft_rekomendasi,
                'file_draft' => $file_path,
                'catatan_evaluasi' => $request->catatan_evaluasi,
                'evaluated_by' => Auth::id(),
                'evaluated_at' => now(),
            ]);
        }

        // Update status permohonan
        $permohonan->update([
            'status' => 'draft_recommendation',
        ]);

        return redirect()->route('evaluasi.index')->with('success', 'Draft rekomendasi berhasil disimpan.');
    }

    public function downloadDraft(Evaluasi $evaluasi)
    {
        if (!$evaluasi->file_draft) {
            abort(404, 'File draft tidak ditemukan.');
        }

        return Storage::disk('public')->download($evaluasi->file_draft);
    }
}