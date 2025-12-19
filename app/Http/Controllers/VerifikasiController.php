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
        $query = Permohonan::with(['kabupatenKota', 'permohonanDokumen.masterKelengkapan'])
            ->whereIn('status_akhir', ['proses', 'revisi', 'selesai']); // Tampilkan semua status verifikasi

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('kabupatenKota', function ($subQ) use ($request) {
                    $subQ->where('nama', 'like', '%' . $request->search . '%');
                })
                    ->orWhere('jenis_dokumen', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status verifikasi
        if ($request->filled('status')) {
            $query->where('status_akhir', $request->status);
        }

        $permohonan = $query->latest('submitted_at')->paginate(10);

        return view('verifikasi.index', compact('permohonan'));
    }

    public function show(Permohonan $permohonan)
    {
        // Load data lengkap dengan proper relations
        $permohonan->load([
            'kabupatenKota',
            'jadwalFasilitasi',
            'permohonanDokumen.masterKelengkapan'
        ]);

        return view('verifikasi.show', compact('permohonan'));
    }

    public function verifikasi(Request $request, Permohonan $permohonan)
    {
        // Validasi
        $request->validate([
            'dokumen' => 'required|array',
            'dokumen.*.status_verifikasi' => 'required|in:verified,revision',
            'catatan_umum' => 'nullable|string',
            'status_verifikasi' => 'required|in:verified,revision',
        ]);

        // Update dokumen verifikasi
        $allVerified = true;
        foreach ($request->dokumen as $dokumenId => $data) {
            $dokumen = PermohonanDokumen::findOrFail($dokumenId);
            $dokumen->update([
                'status_verifikasi' => $data['status_verifikasi'],
                'catatan_verifikasi' => $data['catatan'] ?? null,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            if ($data['status_verifikasi'] === 'revision') {
                $allVerified = false;
            }
        }

        // Update status permohonan berdasarkan hasil verifikasi
        $newStatus = $request->status_verifikasi === 'verified' && $allVerified ? 'selesai' : 'revisi';

        $permohonan->update([
            'status_akhir' => $newStatus,
        ]);

        $message = $newStatus === 'selesai'
            ? 'Verifikasi berhasil! Dokumen lengkap dan dapat dilanjutkan ke evaluasi.'
            : 'Verifikasi selesai! Dokumen perlu revisi oleh pemohon.';

        return redirect()->route('verifikasi.index')->with('success', $message);
    }

    /**
     * Verifikasi per dokumen (AJAX)
     */
    public function verifikasiDokumen(Request $request, Permohonan $permohonan)
    {
        // Validasi
        $request->validate([
            'dokumen_id' => 'required|exists:permohonan_dokumen,id',
            'status_verifikasi' => 'required|in:verified,revision',
            'catatan' => 'nullable|string',
        ]);

        // Cari dokumen
        $dokumen = PermohonanDokumen::where('id', $request->dokumen_id)
            ->where('permohonan_id', $permohonan->id)
            ->firstOrFail();

        // Update status verifikasi dokumen
        $dokumen->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_verifikasi' => $request->catatan,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Jika status = revision, reset file agar pemohon upload ulang
        if ($request->status_verifikasi === 'revision') {
            $dokumen->update([
                'file_path' => null,
                'file_name' => null,
                'is_ada' => false,
            ]);
        }

        // Cek apakah semua dokumen sudah verified
        $totalDokumen = $permohonan->permohonanDokumen->count();
        $verifiedDokumen = $permohonan->permohonanDokumen
            ->where('status_verifikasi', 'verified')
            ->count();
        $revisiDokumen = $permohonan->permohonanDokumen
            ->where('status_verifikasi', 'revision')
            ->count();

        // Update status permohonan dan tahapan
        if ($verifiedDokumen === $totalDokumen) {
            // Semua dokumen verified
            $permohonan->update(['status_akhir' => 'selesai']);

            // Update/create tahapan Verifikasi di permohonan_tahapan
            $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();

            if ($masterTahapanVerifikasi) {
                \App\Models\PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanVerifikasi->id,
                    ],
                    [
                        'status' => 'selesai',
                        'catatan' => 'Verifikasi dokumen selesai - semua dokumen terverifikasi pada ' . now()->format('d M Y H:i'),
                        'updated_by' => Auth::id(),
                    ]
                );
            }
        } elseif ($revisiDokumen > 0) {
            // Ada dokumen yang perlu revisi
            $permohonan->update(['status_akhir' => 'revisi']);

            // Update tahapan Verifikasi menjadi status revisi
            $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();

            if ($masterTahapanVerifikasi) {
                \App\Models\PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanVerifikasi->id,
                    ],
                    [
                        'status' => 'revisi',
                        'catatan' => 'Dokumen perlu revisi - pemohon diminta memperbaiki dokumen pada ' . now()->format('d M Y H:i'),
                        'updated_by' => Auth::id(),
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi dokumen berhasil disimpan',
            'data' => [
                'status_verifikasi' => $dokumen->status_verifikasi,
                'catatan' => $dokumen->catatan_verifikasi,
                'status_permohonan' => $permohonan->status_akhir,
            ]
        ]);
    }
}
