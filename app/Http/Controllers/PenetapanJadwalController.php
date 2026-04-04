<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PenetapanJadwalFasilitasi;
use App\Models\JadwalFasilitasi;
use App\Models\Notifikasi;
use App\Models\User;
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
        $query = Permohonan::with(['kabupatenKota', 'laporanVerifikasi', 'penetapanJadwal', 'jenisDokumen'])
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
        ]);

        // Load relasi untuk activity log
        $permohonan->load(['kabupatenKota', 'jenisDokumen']);

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
                'ditetapkan_oleh' => Auth::id(),
                'tanggal_penetapan' => now(),
            ]);

            // Update tahapan permohonan - tetap status 'proses'
            try {
                $tahapanPenetapan = \App\Models\MasterTahapan::where('nama_tahapan', 'Penetapan Jadwal')->first();
                if ($tahapanPenetapan) {
                    $permohonan->tahapan()->updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanPenetapan->id
                        ],
                        [
                            'status' => 'proses',
                            'catatan' => sprintf(
                                'Jadwal fasilitasi telah ditetapkan (%s s/d %s). Menunggu pembuatan undangan pelaksanaan.',
                                \Carbon\Carbon::parse($request->tanggal_mulai)->format('d M Y'),
                                \Carbon\Carbon::parse($request->tanggal_selesai)->format('d M Y')
                            ),
                            'updated_by' => Auth::id(),
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Log error tapi tetap lanjutkan
                Log::warning('Gagal update tahapan: ' . $e->getMessage());
            }

            // Activity log
            activity()
                ->performedOn($permohonan)
                ->causedBy(auth()->user())
                ->withProperties([
                    'penetapan_id' => $penetapan->id,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'lokasi' => $request->lokasi,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? null,
                    'jenis_dokumen' => $permohonan->jenisDokumen->nama_dokumen ?? null,
                    'tahun' => $permohonan->tahun,
                ])
                ->log('Jadwal fasilitasi ditetapkan oleh Kaban');

            Log::info('Jadwal fasilitasi ditetapkan', [
                'permohonan_id' => $permohonan->id,
                'penetapan_id' => $penetapan->id,
                'ditetapkan_oleh' => auth()->user()->name,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            // Kirim notifikasi ke admin, tim fedora, dan pemohon (database + WhatsApp)
            $notificationService = app(\App\Services\PermohonanNotificationService::class);
            $notificationService->notifyJadwalDitetapkan($permohonan);

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

    /**
     * Update penetapan jadwal
     */
    public function update(Request $request, Permohonan $permohonan)
    {
        $penetapan = $permohonan->penetapanJadwal;

        if (!$penetapan) {
            return redirect()->route('penetapan-jadwal.create', $permohonan)
                ->with('error', 'Jadwal fasilitasi belum ditetapkan.');
        }

        $request->validate([
            'jadwal_fasilitasi_id' => 'nullable|exists:jadwal_fasilitasi,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Load relasi untuk activity log
        $permohonan->load(['kabupatenKota', 'jenisDokumen']);

        try {
            DB::beginTransaction();

            // Simpan data lama untuk perbandingan
            $dataLama = [
                'tanggal_mulai' => $penetapan->tanggal_mulai,
                'tanggal_selesai' => $penetapan->tanggal_selesai,
                'lokasi' => $penetapan->lokasi,
                'latitude' => $penetapan->latitude,
                'longitude' => $penetapan->longitude,
            ];

            // Update penetapan jadwal
            $penetapan->update([
                'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'lokasi' => $request->lokasi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'diubah_oleh' => Auth::id(),
                'tanggal_perubahan' => now(),
            ]);

            // Update tahapan permohonan jika ada
            try {
                $tahapanPenetapan = \App\Models\MasterTahapan::where('nama_tahapan', 'Penetapan Jadwal')->first();
                if ($tahapanPenetapan) {
                    $permohonan->tahapan()->updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanPenetapan->id
                        ],
                        [
                            'catatan' => sprintf(
                                'Jadwal fasilitasi telah diperbarui (%s s/d %s). Menunggu pembuatan undangan pelaksanaan.',
                                \Carbon\Carbon::parse($request->tanggal_mulai)->format('d M Y'),
                                \Carbon\Carbon::parse($request->tanggal_selesai)->format('d M Y')
                            ),
                            'updated_by' => Auth::id(),
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Gagal update tahapan: ' . $e->getMessage());
            }

            // Activity log
            activity()
                ->performedOn($permohonan)
                ->causedBy(auth()->user())
                ->withProperties([
                    'penetapan_id' => $penetapan->id,
                    'data_lama' => $dataLama,
                    'data_baru' => [
                        'tanggal_mulai' => $request->tanggal_mulai,
                        'tanggal_selesai' => $request->tanggal_selesai,
                        'lokasi' => $request->lokasi,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ],
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? null,
                    'jenis_dokumen' => $permohonan->jenisDokumen->nama_dokumen ?? null,
                    'tahun' => $permohonan->tahun,
                ])
                ->log('Jadwal fasilitasi diperbarui oleh Kaban');

            Log::info('Jadwal fasilitasi diperbarui', [
                'permohonan_id' => $permohonan->id,
                'penetapan_id' => $penetapan->id,
                'diubah_oleh' => auth()->user()->name,
                'tanggal_mulai_lama' => $dataLama['tanggal_mulai'],
                'tanggal_mulai_baru' => $request->tanggal_mulai,
            ]);

            // Kirim notifikasi update jadwal ke admin, tim fedora, dan pemohon (database + WhatsApp)
            $notificationService = app(\App\Services\PermohonanNotificationService::class);
            $notificationService->notifyJadwalDiubah($permohonan);

            DB::commit();

            return redirect()->route('penetapan-jadwal.show', $permohonan)
                ->with('success', 'Jadwal fasilitasi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update penetapan jadwal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }
}
