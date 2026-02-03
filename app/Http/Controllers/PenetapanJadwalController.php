<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PenetapanJadwalFasilitasi;
use App\Models\JadwalFasilitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenetapanJadwalController extends Controller
{
    /**
     * Tampilkan daftar permohonan yang perlu penetapan jadwal
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'laporanVerifikasi', 'penetapanJadwal'])
            ->whereHas('laporanVerifikasi', function ($q) {
                $q->where('status_kelengkapan', 'lengkap');
            });

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status jadwal
        if ($request->filled('status_jadwal')) {
            if ($request->status_jadwal == 'belum_ditetapkan') {
                $query->doesntHave('penetapanJadwal');
            } else {
                $query->has('penetapanJadwal');
            }
        }

        $permohonan = $query->latest()->paginate(10);

        return view('pages.penetapan-jadwal.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk menetapkan jadwal
     */
    public function create(Permohonan $permohonan)
    {
        // Cek apakah sudah ada penetapan jadwal
        if ($permohonan->penetapanJadwal) {
            return redirect()->route('penetapan-jadwal.show', $permohonan)
                ->with('info', 'Jadwal fasilitasi sudah ditetapkan sebelumnya.');
        }

        // Ambil jadwal fasilitasi yang tersedia
        $jadwalTersedia = JadwalFasilitasi::where('status', 'published')
            ->where('tanggal_mulai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->get();

        return view('pages.penetapan-jadwal.create', compact('permohonan', 'jadwalTersedia'));
    }

    /**
     * Simpan penetapan jadwal
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'nullable|exists:jadwal_fasilitasi,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'catatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Buat penetapan jadwal
            $penetapan = PenetapanJadwalFasilitasi::create([
                'permohonan_id' => $permohonan->id,
                'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'lokasi' => $request->lokasi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
                'tanggal_penetapan' => now(),
            ]);

            // Update tahapan permohonan (opsional, skip jika error)
            try {
                $tahapanPenetapan = \App\Models\MasterTahapan::where('nama_tahapan', 'Penetapan Jadwal')->first();
                if ($tahapanPenetapan) {
                    $permohonan->tahapan()->updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanPenetapan->id
                        ],
                        [
                            'status' => 'selesai',
                            'tgl_mulai' => now(),
                            'tgl_selesai' => now(),
                            'catatan' => 'Jadwal fasilitasi telah ditetapkan',
                        ]
                    );
                }

                // Mulai tahapan pelaksanaan
                $tahapanPelaksanaan = \App\Models\MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->first();
                if ($tahapanPelaksanaan) {
                    $permohonan->tahapan()->updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanPelaksanaan->id
                        ],
                        [
                            'status' => 'proses',
                            'tgl_mulai' => $request->tanggal_mulai,
                            'catatan' => 'Menunggu pelaksanaan fasilitasi',
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Log error tapi tetap lanjutkan
                Log::warning('Gagal update tahapan: ' . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('penetapan-jadwal.show', $permohonan)
                ->with('success', 'Jadwal fasilitasi berhasil ditetapkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error penetapan jadwal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menetapkan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail penetapan jadwal
     */
    public function show(Permohonan $permohonan)
    {
        $penetapan = $permohonan->penetapanJadwal;

        if (!$penetapan) {
            return redirect()->route('penetapan-jadwal.create', $permohonan)
                ->with('info', 'Jadwal fasilitasi belum ditetapkan.');
        }

        return view('pages.penetapan-jadwal.show', compact('permohonan', 'penetapan'));
    }
}
