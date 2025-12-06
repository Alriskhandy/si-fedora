<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PenetapanPerda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenetapanPerdaController extends Controller
{
    /**
     * Tampilkan daftar permohonan untuk penetapan PERDA (Kaban)
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'tindakLanjut', 'penetapanPerda'])
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
     * Form penetapan PERDA/PERKADA
     */
    public function create(Permohonan $permohonan)
    {
        // Pastikan tindak lanjut sudah ada
        if (!$permohonan->tindakLanjut) {
            return redirect()->route('penetapan-perda.index')
                ->with('error', 'Tindak lanjut belum ada.');
        }

        $penetapanPerda = $permohonan->penetapanPerda;

        return view('penetapan-perda.create', compact('permohonan', 'penetapanPerda'));
    }

    /**
     * Simpan penetapan PERDA/PERKADA
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'jenis_penetapan' => 'required|in:perda,perkada',
            'nomor_penetapan' => 'required|string',
            'tanggal_penetapan' => 'required|date',
            'tentang' => 'required|string',
            'file_penetapan' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // Upload file
            $filePath = $request->file('file_penetapan')->store('penetapan-perda', 'public');

            // Simpan atau update penetapan PERDA
            $penetapanPerda = PenetapanPerda::updateOrCreate(
                ['permohonan_id' => $permohonan->id],
                [
                    'nomor_perda' => $request->nomor_penetapan,
                    'tanggal_penetapan' => $request->tanggal_penetapan,
                    'file_perda' => $filePath,
                    'keterangan' => $request->tentang,
                    'dibuat_oleh' => Auth::id(),
                ]
            );

            DB::commit();

            return redirect()->route('penetapan-perda.index')
                ->with('success', 'Penetapan PERDA/PERKADA berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan penetapan PERDA: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan penetapan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail penetapan
     */
    public function show(Permohonan $permohonan)
    {
        $penetapanPerda = $permohonan->penetapanPerda;

        if (!$penetapanPerda) {
            return redirect()->back()->with('error', 'Penetapan belum ada.');
        }

        return view('penetapan-perda.show', compact('permohonan', 'penetapanPerda'));
    }

    /**
     * Download file penetapan
     */
    public function download(Permohonan $permohonan)
    {
        $penetapanPerda = $permohonan->penetapanPerda;

        if (!$penetapanPerda) {
            return back()->with('error', 'Penetapan tidak ditemukan.');
        }

        $filePath = $penetapanPerda->file_perda;

        if (!$filePath) {
            return back()->with('error', 'File penetapan tidak ditemukan.');
        }

        $filepath = storage_path('app/public/' . $filePath);

        if (!file_exists($filepath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        return response()->download($filepath);
    }

    /**
     * Daftar penetapan untuk publik
     */
    public function public(Request $request)
    {
        $query = PenetapanPerda::with(['permohonan.kabupatenKota'])
            ->whereNotNull('file_perda');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('permohonan.kabupatenKota', function ($q) use ($request) {
                $q->where('nama_kabkota', 'like', '%' . $request->search . '%');
            });
        }

        // Filter jenis
        if ($request->filled('jenis')) {
            $query->where('jenis_penetapan', $request->jenis)
                ->orWhere(function ($q) use ($request) {
                    // Fallback jika jenis_penetapan null, anggap PERDA
                    $q->whereNull('jenis_penetapan')
                        ->where('jenis', $request->jenis == 'perda' ? 'perda' : 'perkada');
                });
        }

        $penetapans = $query->latest()->paginate(15);

        return view('penetapan-perda.public', compact('penetapans'));
    }
}
