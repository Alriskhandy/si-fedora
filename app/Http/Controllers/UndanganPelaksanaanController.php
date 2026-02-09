<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\UndanganPelaksanaan;
use App\Models\UndanganPenerima;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UndanganPelaksanaanController extends Controller
{
    /**
     * Tampilkan daftar permohonan yang perlu undangan
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'penetapanJadwal', 'undanganPelaksanaan'])
            ->whereHas('penetapanJadwal');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status undangan
        if ($request->filled('status_undangan')) {
            if ($request->status_undangan == 'belum_ada') {
                $query->doesntHave('undanganPelaksanaan');
            } else if ($request->status_undangan == 'draft') {
                $query->whereHas('undanganPelaksanaan', function ($q) {
                    $q->where('status', 'draft');
                });
            } else {
                $query->whereHas('undanganPelaksanaan', function ($q) {
                    $q->where('status', 'terkirim');
                });
            }
        }

        $permohonan = $query->latest()->paginate(10);

        return view('pages.undangan-pelaksanaan.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk membuat undangan
     */
    public function create(Permohonan $permohonan)
    {
        if ($permohonan->undanganPelaksanaan) {
            return redirect()->route('undangan-pelaksanaan.show', $permohonan)
                ->with('info', 'Undangan pelaksanaan sudah dibuat sebelumnya.');
        }

        if (!$permohonan->penetapanJadwal) {
            return redirect()->route('undangan-pelaksanaan.index')
                ->with('error', 'Jadwal fasilitasi belum ditetapkan.');
        }

        $timData = $this->getTimAssignments($permohonan);

        return view('pages.undangan-pelaksanaan.create', array_merge(
            ['permohonan' => $permohonan],
            $timData
        ));
    }

    /**
     * Simpan undangan
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'file_undangan' => 'required|file|mimes:pdf|max:2048',
            'penerima' => 'required|array|min:1',
            'penerima.*' => 'required|exists:users,id',
        ]);

        // Load relasi yang diperlukan
        $permohonan->load(['kabupatenKota', 'jenisDokumen', 'penetapanJadwal']);

        try {
            DB::beginTransaction();

            $filePath = $request->hasFile('file_undangan')
                ? $request->file('file_undangan')->store('undangan', 'public')
                : null;

            $undangan = UndanganPelaksanaan::create([
                'permohonan_id' => $permohonan->id,
                'penetapan_jadwal_id' => $permohonan->penetapanJadwal->id,
                'file_undangan' => $filePath,
                'status' => 'terkirim',
                'dibuat_oleh' => Auth::id(),
                'tanggal_dikirim' => now(),
            ]);

            $this->attachPenerima($undangan, $request->penerima);
            $this->updateTahapan($permohonan);

            DB::commit();

            $this->logActivity($undangan, $permohonan, count($request->penerima));
            $this->sendNotifications($undangan, $permohonan, $request->penerima);

            return redirect()->route('undangan-pelaksanaan.show', $permohonan)
                ->with('success', 'Undangan pelaksanaan berhasil dibuat dan dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat undangan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat undangan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail undangan
     */
    public function show(Permohonan $permohonan)
    {
        $undangan = $permohonan->undanganPelaksanaan;

        if (!$undangan) {
            return redirect()->route('undangan-pelaksanaan.create', $permohonan)
                ->with('info', 'Undangan pelaksanaan belum dibuat.');
        }

        $undangan->load('penerima.user');

        return view('pages.undangan-pelaksanaan.show', compact('permohonan', 'undangan'));
    }

    /**
     * Download file undangan
     */
    public function download(Permohonan $permohonan)
    {
        $undangan = $permohonan->undanganPelaksanaan;

        if (!$undangan || !$undangan->file_undangan) {
            return back()->with('error', 'File undangan tidak ditemukan.');
        }

        // Log activity
        activity()
            ->performedOn($undangan)
            ->causedBy(Auth::user())
            ->withProperties([
                'permohonan_id' => $permohonan->id,
                'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                'file' => $undangan->file_undangan,
            ])
            ->log('Download file undangan pelaksanaan');

        return Storage::disk('public')->download($undangan->file_undangan);
    }

    /**
     * Daftar undangan yang diterima user (untuk pemohon, verifikator, fasilitator)
     */
    public function myUndangan(Request $request)
    {
        $query = UndanganPenerima::with(['undangan.permohonan.kabupatenKota', 'undangan.penetapanJadwal'])
            ->where('user_id', Auth::id());

        // Filter status baca
        if ($request->filled('status_baca')) {
            if ($request->status_baca == 'belum_dibaca') {
                $query->where('dibaca', false);
            } else {
                $query->where('dibaca', true);
            }
        }

        $undanganList = $query->latest()->paginate(10);

        return view('pages.undangan-pelaksanaan.my-undangan', compact('undanganList'));
    }

    /**
     * Lihat detail undangan (untuk penerima)
     */
    public function view($id)
    {
        $undanganPenerima = UndanganPenerima::with(['undangan.permohonan.kabupatenKota', 'undangan.penetapanJadwal'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if (!$undanganPenerima->dibaca) {
            $undanganPenerima->markAsRead();
        }

        return view('pages.undangan-pelaksanaan.view', compact('undanganPenerima'));
    }

    /**
     * Get tim assignments untuk kabupaten/kota
     */
    private function getTimAssignments(Permohonan $permohonan): array
    {
        $kabkotaId = $permohonan->kab_kota_id;
        $tahun = $permohonan->tahun;

        $assignmentQuery = function ($roleType) use ($kabkotaId, $tahun) {
            return function ($q) use ($kabkotaId, $tahun, $roleType) {
                $q->where('kabupaten_kota_id', $kabkotaId)
                  ->where('role_type', $roleType)
                  ->where('tahun', $tahun)
                  ->where('is_active', true);
            };
        };

        $verifikatorList = User::whereHas('kabkotaAssignments', $assignmentQuery('verifikator'))
            ->with(['kabkotaAssignments' => $assignmentQuery('verifikator')])
            ->get();

        $fasilitatorList = User::whereHas('kabkotaAssignments', $assignmentQuery('fasilitator'))
            ->with(['kabkotaAssignments' => $assignmentQuery('fasilitator')])
            ->get();

        $koordinatorList = User::whereHas('kabkotaAssignments', $assignmentQuery('koordinator'))
            ->with(['kabkotaAssignments' => $assignmentQuery('koordinator')])
            ->get();

        $pemohonList = User::whereHas('roles', fn($q) => $q->where('name', 'pemohon'))
            ->whereHas('kabupatenKota', fn($q) => $q->where('id', $permohonan->kab_kota_id))
            ->get();

        $autoSelectedPenerima = collect()
            ->merge($verifikatorList->pluck('id'))
            ->merge($fasilitatorList->pluck('id'))
            ->merge($koordinatorList->pluck('id'))
            ->merge($pemohonList->pluck('id'))
            ->unique()
            ->toArray();

        return compact('verifikatorList', 'fasilitatorList', 'koordinatorList', 'pemohonList', 'autoSelectedPenerima');
    }

    /**
     * Attach penerima ke undangan
     */
    private function attachPenerima(UndanganPelaksanaan $undangan, array $penerimaIds): void
    {
        foreach ($penerimaIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $jenisPenerima = 'pemohon';
            if ($user->hasRole('verifikator')) {
                $jenisPenerima = 'verifikator';
            } elseif ($user->hasRole('fasilitator')) {
                $jenisPenerima = 'fasilitator';
            }

            UndanganPenerima::create([
                'undangan_id' => $undangan->id,
                'user_id' => $userId,
                'jenis_penerima' => $jenisPenerima,
            ]);
        }
    }

    /**
     * Update tahapan permohonan
     */
    private function updateTahapan(Permohonan $permohonan): void
    {
        // Set tahapan Penetapan Jadwal menjadi selesai
        $tahapanPenetapanJadwal = \App\Models\MasterTahapan::where('nama_tahapan', 'Penetapan Jadwal')->first();
        
        if (!$tahapanPenetapanJadwal) {
            Log::warning('Tahapan Penetapan Jadwal tidak ditemukan di master_tahapan');
        } else {
            $updated = \App\Models\PermohonanTahapan::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'tahapan_id' => $tahapanPenetapanJadwal->id],
                [
                    'status' => 'selesai',
                    'tanggal_selesai' => now(),
                    'keterangan' => 'Undangan pelaksanaan telah dibuat dan dikirim'
                ]
            );
            Log::info('Tahapan Penetapan Jadwal updated', ['permohonan_id' => $permohonan->id, 'updated' => $updated->wasRecentlyCreated ? 'created' : 'updated']);
        }

        // Set tahapan Pelaksanaan Fasilitasi menjadi proses
        $tahapanPelaksanaan = \App\Models\MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->first();
        
        if (!$tahapanPelaksanaan) {
            Log::warning('Tahapan Pelaksanaan Fasilitasi tidak ditemukan di master_tahapan');
        } else {
            $updated = \App\Models\PermohonanTahapan::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'tahapan_id' => $tahapanPelaksanaan->id],
                [
                    'status' => 'proses',
                    'tanggal_mulai' => now(),
                    'keterangan' => 'Menunggu pelaksanaan kegiatan fasilitasi'
                ]
            );
            Log::info('Tahapan Pelaksanaan Fasilitasi updated', ['permohonan_id' => $permohonan->id, 'updated' => $updated->wasRecentlyCreated ? 'created' : 'updated']);
        }
    }

    /**
     * Log activity
     */
    private function logActivity(UndanganPelaksanaan $undangan, Permohonan $permohonan, int $jumlahPenerima): void
    {
        activity()
            ->performedOn($undangan)
            ->causedBy(Auth::user())
            ->withProperties([
                'permohonan_id' => $permohonan->id,
                'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                'jumlah_penerima' => $jumlahPenerima,
            ])
            ->log('Membuat dan mengirim undangan pelaksanaan');
    }

    /**
     * Kirim notifikasi ke penerima
     */
    private function sendNotifications(UndanganPelaksanaan $undangan, Permohonan $permohonan, array $penerimaIds): void
    {
        $jadwal = $permohonan->penetapanJadwal;

        foreach ($penerimaIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            try {
                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Undangan Pelaksanaan Fasilitasi',
                    'message' => sprintf(
                        'Anda diundang untuk mengikuti kegiatan Fasilitasi Penyusunan RKPD %s - %s tahun %s. Pelaksanaan: %s s/d %s%s. Silakan download file undangan lengkap.',
                        $permohonan->kabupatenKota->nama ?? 'N/A',
                        $permohonan->jenisDokumen->nama_dokumen ?? 'N/A',
                        $permohonan->tahun,
                        $jadwal->tanggal_mulai->format('d M Y'),
                        $jadwal->tanggal_selesai->format('d M Y'),
                        $jadwal->lokasi ? ' di ' . $jadwal->lokasi : ''
                    ),
                    'type' => 'info',
                    'action_url' => route('my-undangan.view', $undangan->penerima()->where('user_id', $user->id)->first()->id ?? $undangan->id),
                    'notifiable_type' => UndanganPelaksanaan::class,
                    'notifiable_id' => $undangan->id,
                ]);

                Log::info('Notifikasi undangan dikirim', ['user_id' => $user->id, 'user_name' => $user->name]);
            } catch (\Exception $e) {
                Log::error('Gagal mengirim notifikasi', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
