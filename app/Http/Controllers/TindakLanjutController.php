<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    /**
     * Tampilkan daftar permohonan untuk tindak lanjut (Pemohon)
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'hasilFasilitasi', 'tindakLanjut'])
            ->where('user_id', Auth::id())
            ->whereHas('hasilFasilitasi', function ($q) {
                $q->where('status_validasi', 'disetujui');
            });

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where('no_permohonan', 'like', '%' . $request->search . '%');
        }

        $permohonans = $query->latest()->paginate(10);

        return view('tindak-lanjut.index', compact('permohonans'));
    }

    /**
     * Form upload tindak lanjut
     */
    public function create(Permohonan $permohonan)
    {
        // Pastikan permohonan milik user yang login
        if ($permohonan->user_id !== Auth::id()) {
            return redirect()->route('tindak-lanjut.index')
                ->with('error', 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Pastikan hasil fasilitasi sudah disetujui
        if (!$permohonan->hasilFasilitasi || $permohonan->hasilFasilitasi->status_validasi !== 'disetujui') {
            return redirect()->route('tindak-lanjut.index')
                ->with('error', 'Hasil fasilitasi belum disetujui.');
        }

        $tindakLanjut = $permohonan->tindakLanjut;

        return view('tindak-lanjut.create', compact('permohonan', 'tindakLanjut'));
    }

    /**
     * Simpan tindak lanjut
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        // Pastikan permohonan milik user yang login
        if ($permohonan->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $request->validate([
            'keterangan' => 'required|string',
            'file_laporan' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // Upload file
            $filePath = $request->file('file_laporan')->store('tindak-lanjut', 'public');

            // Simpan atau update tindak lanjut
            $tindakLanjut = TindakLanjut::updateOrCreate(
                ['permohonan_id' => $permohonan->id],
                [
                    'keterangan' => $request->keterangan,
                    'file_laporan' => $filePath,
                    'tanggal_upload' => now(),
                    'diupload_oleh' => Auth::id(),
                ]
            );

            DB::commit();

            return redirect()->route('tindak-lanjut.index')
                ->with('success', 'Laporan tindak lanjut berhasil diunggah.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan tindak lanjut: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan tindak lanjut: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail tindak lanjut
     */
    public function show(Permohonan $permohonan)
    {
        $tindakLanjut = $permohonan->tindakLanjut;

        if (!$tindakLanjut) {
            return redirect()->back()->with('error', 'Tindak lanjut belum ada.');
        }

        return view('tindak-lanjut.show', compact('permohonan', 'tindakLanjut'));
    }

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
}
