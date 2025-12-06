<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiUrusan;
use App\Models\HasilFasilitasiSistematika;
use App\Models\MasterUrusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HasilFasilitasiController extends Controller
{
    /**
     * Tampilkan daftar permohonan untuk input hasil fasilitasi (Fasilitator)
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'undanganPelaksanaan', 'hasilFasilitasi'])
            ->whereHas('undanganPelaksanaan', function ($q) {
                $q->where('status', 'terkirim')
                    ->whereHas('penerima', function ($penerima) {
                        $penerima->where('user_id', Auth::id())
                            ->where('jenis_penerima', 'fasilitator');
                    });
            });

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status draft
        if ($request->filled('status_draft')) {
            if ($request->status_draft == 'belum_ada') {
                $query->doesntHave('hasilFasilitasi');
            } else {
                $query->whereHas('hasilFasilitasi', function ($q) use ($request) {
                    $q->where('status_draft', $request->status_draft);
                });
            }
        }

        $permohonan = $query->latest()->paginate(10);

        return view('hasil-fasilitasi.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk membuat hasil fasilitasi
     */
    public function create(Permohonan $permohonan)
    {
        // Jika belum ada hasil fasilitasi, buat otomatis
        if (!$permohonan->hasilFasilitasi) {
            HasilFasilitasi::create([
                'permohonan_id' => $permohonan->id,
                'dibuat_oleh' => Auth::id(),
            ]);
            $permohonan->refresh();
        }

        // Ambil daftar urusan
        $masterUrusanList = MasterUrusan::orderBy('urutan')->get();

        // Load hasil fasilitasi dengan relasi
        $hasilFasilitasi = $permohonan->hasilFasilitasi;
        $hasilFasilitasi->load('hasilSistematika', 'hasilUrusan.masterUrusan');

        return view('hasil-fasilitasi.create', compact('permohonan', 'masterUrusanList', 'hasilFasilitasi'));
    }

    /**
     * Simpan hasil fasilitasi
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'ringkasan_hasil' => 'required|string',
            'rekomendasi' => 'required|string',
            'catatan_fasilitator' => 'nullable|string',
            'file_draft' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('file_draft')) {
                // Hapus file lama jika ada
                if ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->file_draft) {
                    Storage::disk('public')->delete($permohonan->hasilFasilitasi->file_draft);
                }
                $filePath = $request->file('file_draft')->store('hasil-fasilitasi', 'public');
            } elseif ($permohonan->hasilFasilitasi) {
                $filePath = $permohonan->hasilFasilitasi->file_draft;
            }

            // Simpan atau update hasil fasilitasi
            $hasilFasilitasi = HasilFasilitasi::updateOrCreate(
                ['permohonan_id' => $permohonan->id],
                [
                    'undangan_id' => $permohonan->undanganPelaksanaan->id ?? null,
                    'ringkasan_hasil' => $request->ringkasan_hasil,
                    'rekomendasi' => $request->rekomendasi,
                    'catatan_fasilitator' => $request->catatan_fasilitator,
                    'file_draft' => $filePath,
                    'status_draft' => 'draft',
                    'dibuat_oleh' => Auth::id(),
                    'tanggal_dibuat' => $permohonan->hasilFasilitasi ? $permohonan->hasilFasilitasi->tanggal_dibuat : now(),
                ]
            );

            DB::commit();

            return redirect()->route('hasil-fasilitasi.create', $permohonan)
                ->with('success', 'Hasil fasilitasi berhasil disimpan sebagai draft. Silakan tambahkan item sistematika atau urusan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan hasil fasilitasi: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan hasil fasilitasi: ' . $e->getMessage());
        }
    }
    /**
     * Tampilkan detail hasil fasilitasi
     */
    public function show(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi) {
            return redirect()->route('hasil-fasilitasi.create', $permohonan)
                ->with('info', 'Hasil fasilitasi belum dibuat.');
        }

        $hasilFasilitasi->load('hasilUrusan.masterUrusan', 'hasilSistematika');

        return view('hasil-fasilitasi.show', compact('permohonan', 'hasilFasilitasi'));
    }

    /**
     * Ajukan hasil fasilitasi ke admin_peran
     */
    public function submit(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi) {
            return back()->with('error', 'Hasil fasilitasi tidak ditemukan.');
        }

        if (!$hasilFasilitasi->canEdit()) {
            return back()->with('info', 'Hasil fasilitasi sudah diajukan/disetujui.');
        }

        try {
            $hasilFasilitasi->update([
                'status_draft' => 'diajukan',
                'tanggal_diajukan' => now(),
            ]);

            return back()->with('success', 'Hasil fasilitasi berhasil diajukan untuk validasi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan hasil fasilitasi: ' . $e->getMessage());
        }
    }

    /**
     * Download file draft
     */
    public function download(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi || !$hasilFasilitasi->file_draft) {
            return back()->with('error', 'File draft tidak ditemukan.');
        }

        return Storage::disk('public')->download($hasilFasilitasi->file_draft);
    }

    /**
     * Simpan item sistematika
     */
    public function storeSistematika(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'bab_sub_bab' => 'required|string',
            'catatan_penyempurnaan' => 'required|string',
        ]);

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return response()->json(['error' => 'Hasil fasilitasi belum dibuat'], 400);
            }

            $sistematika = HasilFasilitasiSistematika::create([
                'hasil_fasilitasi_id' => $hasilFasilitasi->id,
                'bab_sub_bab' => $request->bab_sub_bab,
                'catatan_penyempurnaan' => $request->catatan_penyempurnaan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item sistematika berhasil ditambahkan',
                'data' => $sistematika
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus item sistematika
     */
    public function deleteSistematika(Permohonan $permohonan, $id)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return response()->json(['error' => 'Hasil fasilitasi tidak ditemukan'], 404);
            }

            $sistematika = HasilFasilitasiSistematika::where('hasil_fasilitasi_id', $hasilFasilitasi->id)
                ->where('id', $id)
                ->first();

            if (!$sistematika) {
                return response()->json(['error' => 'Item tidak ditemukan'], 404);
            }

            $sistematika->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item sistematika berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Simpan item urusan
     */
    public function storeUrusan(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'master_urusan_id' => 'required|exists:master_urusan,id',
            'catatan_masukan' => 'required|string',
        ]);

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return response()->json(['error' => 'Hasil fasilitasi belum dibuat'], 400);
            }

            // Cek duplikat urusan
            $exists = HasilFasilitasiUrusan::where('hasil_fasilitasi_id', $hasilFasilitasi->id)
                ->where('master_urusan_id', $request->master_urusan_id)
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Urusan ini sudah ditambahkan'], 400);
            }

            $urusan = HasilFasilitasiUrusan::create([
                'hasil_fasilitasi_id' => $hasilFasilitasi->id,
                'master_urusan_id' => $request->master_urusan_id,
                'catatan_masukan' => $request->catatan_masukan,
            ]);

            $urusan->load('masterUrusan');

            return response()->json([
                'success' => true,
                'message' => 'Item urusan berhasil ditambahkan',
                'data' => $urusan
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus item urusan
     */
    public function deleteUrusan(Permohonan $permohonan, $id)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return response()->json(['error' => 'Hasil fasilitasi tidak ditemukan'], 404);
            }

            $urusan = HasilFasilitasiUrusan::where('hasil_fasilitasi_id', $hasilFasilitasi->id)
                ->where('id', $id)
                ->first();

            if (!$urusan) {
                return response()->json(['error' => 'Item tidak ditemukan'], 404);
            }

            $urusan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item urusan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
