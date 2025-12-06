<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\HasilFasilitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratPenyampaianHasilController extends Controller
{
    /**
     * Tampilkan daftar hasil fasilitasi untuk input surat penyampaian (Kaban)
     */
    public function index(Request $request)
    {
        $query = HasilFasilitasi::with(['permohonan.kabupatenKota', 'pembuat'])
            ->whereNotNull('draft_file')
            ->orWhereNotNull('final_file');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('permohonan.kabupatenKota', function ($q) use ($request) {
                $q->where('nama_kabkota', 'like', '%' . $request->search . '%');
            });
        }

        $hasilList = $query->latest('updated_at')->paginate(10);

        return view('surat-penyampaian-hasil.index', compact('hasilList'));
    }

    /**
     * Form input surat penyampaian
     */
    public function create(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi) {
            return redirect()->route('surat-penyampaian-hasil.index')
                ->with('error', 'Hasil fasilitasi tidak ditemukan.');
        }

        return view('surat-penyampaian-hasil.create', compact('permohonan', 'hasilFasilitasi'));
    }

    /**
     * Simpan surat penyampaian
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'surat_penyampaian' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return back()->with('error', 'Hasil fasilitasi tidak ditemukan.');
            }

            // Upload file
            $filePath = $request->file('surat_penyampaian')->store('surat-penyampaian-hasil', 'public');

            // Update hasil fasilitasi
            $hasilFasilitasi->update([
                'surat_penyampaian' => $filePath,
                'surat_dibuat_oleh' => Auth::id(),
                'surat_tanggal' => now(),
            ]);

            DB::commit();

            return redirect()->route('surat-penyampaian-hasil.index')
                ->with('success', 'Surat Penyampaian Hasil Fasilitasi berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error upload surat penyampaian: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal upload surat: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail surat penyampaian
     */
    public function show(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi || !$hasilFasilitasi->surat_penyampaian) {
            return redirect()->back()->with('error', 'Surat penyampaian tidak ditemukan.');
        }

        return view('surat-penyampaian-hasil.show', compact('permohonan', 'hasilFasilitasi'));
    }

    /**
     * Download surat penyampaian (semua role)
     */
    public function download(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi || !$hasilFasilitasi->surat_penyampaian) {
            return back()->with('error', 'Surat penyampaian tidak ditemukan.');
        }

        $filepath = storage_path('app/public/' . $hasilFasilitasi->surat_penyampaian);

        if (!file_exists($filepath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        return response()->download($filepath);
    }

    /**
     * Daftar publik surat penyampaian (semua role dapat melihat)
     */
    public function publicList(Request $request)
    {
        $query = HasilFasilitasi::with(['permohonan.kabupatenKota', 'pembuatSurat'])
            ->whereNotNull('surat_penyampaian');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('permohonan.kabupatenKota', function ($q) use ($request) {
                $q->where('nama_kabkota', 'like', '%' . $request->search . '%');
            });
        }

        $hasilList = $query->latest('surat_tanggal')->paginate(15);

        return view('surat-penyampaian-hasil.public', compact('hasilList'));
    }
}
