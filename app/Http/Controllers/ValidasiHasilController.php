<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\HasilFasilitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
