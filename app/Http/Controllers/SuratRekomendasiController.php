<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratRekomendasiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jenisDokumen'])
            ->where('status', 'approved_by_kaban');

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('jenisDokumen', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $permohonan = $query->latest()->paginate(10);

        return view('pages.surat-rekomendasi.index', compact('permohonan'));
    }

    public function create(Permohonan $permohonan)
    {
        // Pastikan statusnya approved_by_kaban
        if ($permohonan->status !== 'approved_by_kaban') {
            abort(403, 'Permohonan belum disetujui oleh Kaban.');
        }

        return view('pages.surat-rekomendasi.create', compact('permohonan'));
    }

    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'nomor_surat' => 'required|string|unique:surat_rekomendasi,nomor_surat',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:200',
            'isi_surat' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Simpan surat rekomendasi (bisa pake model baru atau update permohonan)
        $file_path = null;
        if ($request->hasFile('file_path')) {
            $file_path = $request->file('file_path')->store('surat-rekomendasi', 'public');
        }

        $permohonan->update([
            'status' => 'letter_issued',
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'perihal' => $request->perihal,
            'isi_surat' => $request->isi_surat,
            'file_surat' => $file_path,
            'issued_at' => now(),
            'issued_by' => Auth::id(),
        ]);

        return redirect()->route('surat-rekomendasi.index')->with('success', 'Surat rekomendasi berhasil diterbitkan.');
    }
}