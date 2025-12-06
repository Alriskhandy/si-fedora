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
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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

        if (!$hasilFasilitasi || !$hasilFasilitasi->draft_file) {
            return back()->with('error', 'File draft tidak ditemukan.');
        }

        $filepath = storage_path('app/public/' . $hasilFasilitasi->draft_file);

        if (!file_exists($filepath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        return response()->download($filepath);
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

    /**
     * Generate dokumen hasil fasilitasi dalam format Word
     */
    public function generate(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            $sistematika = $hasilFasilitasi->hasilSistematika()->orderBy('id')->get();
            $urusan = $hasilFasilitasi->hasilUrusan()->with('masterUrusan')->orderBy('id')->get();

            // Create Word document content
            $content = $this->generateDocumentContent($permohonan, $sistematika, $urusan);

            // Save to file
            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.doc';
            $filepath = 'hasil-fasilitasi/' . $filename;

            Storage::disk('public')->put($filepath, $content);

            // Update draft_file in hasil_fasilitasi
            $hasilFasilitasi->update([
                'draft_file' => $filepath,
                'updated_by' => Auth::id()
            ]);

            return response()->download(storage_path('app/public/' . $filepath), $filename, [
                'Content-Type' => 'application/msword'
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Generate HTML content for Word document
     */
    private function generateDocumentContent($permohonan, $sistematika, $urusan)
    {
        $kabkota = $permohonan->kabupatenKota->nama;
        $tahun = date('Y');

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; }
        h1 { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 20px; }
        h2 { font-size: 13pt; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; vertical-align: top; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .no-col { width: 5%; text-align: center; }
        .title-col { width: 25%; }
        .content-col { width: 70%; }
    </style>
</head>
<body>
    <h1>HASIL FASILITASI<br>RANCANGAN AKHIR RKPD ' . strtoupper($kabkota) . ' TAHUN ' . $tahun . '</h1>
    
    <h2>I. Sistematika dan Substansi Rancangan Akhir RKPD</h2>
    <p>Catatan penyempurnaan terhadap sistematika dan rancangan akhir RKPD ' . $kabkota . ', sebagai berikut:</p>
    
    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th class="title-col">Bab/Sub Bab</th>
                <th class="content-col">Catatan Penyempurnaan</th>
            </tr>
        </thead>
        <tbody>';

        if ($sistematika->count() > 0) {
            foreach ($sistematika as $index => $item) {
                $html .= '<tr>
                    <td class="no-col">' . ($index + 1) . '</td>
                    <td><strong>' . htmlspecialchars($item->bab_sub_bab) . '</strong></td>
                    <td>' . nl2br(htmlspecialchars($item->catatan_penyempurnaan)) . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="3" style="text-align: center; font-style: italic;">Tidak ada catatan penyempurnaan</td></tr>';
        }

        $html .= '</tbody>
    </table>
    
    <h2>II. Masukan terkait penyelenggaraan urusan Pemerintah Daerah</h2>
    <p>Masukan terkait penyelenggaraan urusan Pemerintah Daerah sebagai berikut:</p>
    
    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th class="content-col">Catatan Masukan/ Saran</th>
            </tr>
        </thead>
        <tbody>';

        if ($urusan->count() > 0) {
            $currentUrusan = null;
            $urusanIndex = 0;
            $itemIndex = 0;

            foreach ($urusan as $item) {
                if ($currentUrusan !== $item->masterUrusan->nama) {
                    $currentUrusan = $item->masterUrusan->nama;
                    $urusanIndex++;
                    $itemIndex = 0;

                    $html .= '<tr>
                        <td class="no-col">' . $urusanIndex . '</td>
                        <td><strong>Urusan ' . htmlspecialchars($currentUrusan) . '</strong></td>
                    </tr>';
                }

                $itemIndex++;
                $html .= '<tr>
                    <td class="no-col">' . $itemIndex . '.</td>
                    <td>' . nl2br(htmlspecialchars($item->catatan_masukan)) . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="2" style="text-align: center; font-style: italic;">Tidak ada catatan masukan</td></tr>';
        }

        $html .= '</tbody>
    </table>
</body>
</html>';

        return $html;
    }

    /**
     * Generate dokumen hasil fasilitasi dalam format PDF
     */
    public function generatePdf(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            $sistematika = $hasilFasilitasi->hasilSistematika()->orderBy('id')->get();
            $urusan = $hasilFasilitasi->hasilUrusan()
                ->with('masterUrusan')
                ->join('master_urusan', 'hasil_fasilitasi_urusan.master_urusan_id', '=', 'master_urusan.id')
                ->orderBy('master_urusan.urutan')
                ->orderBy('hasil_fasilitasi_urusan.id')
                ->select('hasil_fasilitasi_urusan.*')
                ->get();

            // Prepare data for view
            $data = [
                'permohonan' => $permohonan,
                'kabkota' => $permohonan->kabupatenKota->nama,
                'tahun' => date('Y'),
                'sistematika' => $sistematika,
                'urusan' => $urusan
            ];

            // Generate PDF
            $pdf = PDF::loadView('hasil-fasilitasi.pdf', $data)
                ->setPaper('a4', 'portrait');

            // Save to file
            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.pdf';
            $filepath = 'hasil-fasilitasi/' . $filename;

            Storage::disk('public')->put($filepath, $pdf->output());

            // Update final_file in hasil_fasilitasi
            $hasilFasilitasi->update([
                'final_file' => $filepath,
                'updated_by' => Auth::id()
            ]);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating PDF hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }
}
