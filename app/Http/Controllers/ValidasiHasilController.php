<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\HasilFasilitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ValidasiHasilController extends Controller
{
    /**
     * Tampilkan daftar hasil fasilitasi yang perlu validasi (Admin PERAN)
     */
    public function index(Request $request)
    {
        $query = HasilFasilitasi::with(['permohonan.kabupatenKota', 'pembuat'])
            ->whereIn('status_draft', ['diajukan', 'disetujui', 'revisi']);

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('permohonan.kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status
        if ($request->filled('status_draft')) {
            $query->where('status_draft', $request->status_draft);
        }

        $hasilList = $query->latest('tanggal_diajukan')->paginate(10);

        return view('validasi-hasil.index', compact('hasilList'));
    }

    /**
     * Tampilkan detail untuk validasi
     */
    public function show(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi) {
            return redirect()->route('validasi-hasil.index')
                ->with('error', 'Hasil fasilitasi tidak ditemukan.');
        }

        $hasilFasilitasi->load('hasilUrusan.masterUrusan', 'hasilSistematika');

        return view('validasi-hasil.show', compact('permohonan', 'hasilFasilitasi'));
    }

    /**
     * Setujui hasil fasilitasi
     */
    public function approve(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'catatan_validasi' => 'nullable|string',
        ]);

        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi || $hasilFasilitasi->status_draft !== 'diajukan') {
            return back()->with('error', 'Hasil fasilitasi tidak dapat disetujui.');
        }

        try {
            DB::beginTransaction();

            $hasilFasilitasi->update([
                'status_draft' => 'disetujui',
                'divalidasi_oleh' => Auth::id(),
                'tanggal_validasi' => now(),
                'catatan_validasi' => $request->catatan_validasi,
            ]);

            // Update tahapan (Pelaksanaan = selesai)
            try {
                $tahapanPelaksanaan = \App\Models\MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->first();

                if ($tahapanPelaksanaan) {
                    \App\Models\PermohonanTahapan::updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanPelaksanaan->id
                        ],
                        [
                            'status' => 'selesai',
                            'tanggal_selesai' => now(),
                            'keterangan' => 'Hasil fasilitasi telah disetujui'
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Gagal update tahapan pelaksanaan: ' . $e->getMessage());
            }

            DB::commit();

            return back()->with('success', 'Hasil fasilitasi berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui hasil fasilitasi: ' . $e->getMessage());
        }
    }

    /**
     * Minta revisi hasil fasilitasi
     */
    public function revise(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'catatan_validasi' => 'required|string',
        ]);

        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi || $hasilFasilitasi->status_draft !== 'diajukan') {
            return back()->with('error', 'Hasil fasilitasi tidak dapat direvisi.');
        }

        try {
            $hasilFasilitasi->update([
                'status_draft' => 'revisi',
                'divalidasi_oleh' => Auth::id(),
                'tanggal_validasi' => now(),
                'catatan_validasi' => $request->catatan_validasi,
            ]);

            return back()->with('success', 'Hasil fasilitasi dikembalikan untuk revisi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal meminta revisi: ' . $e->getMessage());
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
