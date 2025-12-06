<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\UndanganPelaksanaan;
use App\Models\UndanganPenerima;
use App\Models\User;
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

        return view('undangan-pelaksanaan.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk membuat undangan
     */
    public function create(Permohonan $permohonan)
    {
        // Cek apakah sudah ada undangan
        if ($permohonan->undanganPelaksanaan) {
            return redirect()->route('undangan-pelaksanaan.show', $permohonan)
                ->with('info', 'Undangan pelaksanaan sudah dibuat sebelumnya.');
        }

        // Cek apakah sudah ada penetapan jadwal
        if (!$permohonan->penetapanJadwal) {
            return redirect()->route('undangan-pelaksanaan.index')
                ->with('error', 'Jadwal fasilitasi belum ditetapkan.');
        }

        // Ambil daftar user untuk penerima
        $verifikatorList = User::whereHas('roles', function ($q) {
            $q->where('name', 'verifikator');
        })->get();

        $fasilitatorList = User::whereHas('roles', function ($q) {
            $q->where('name', 'fasilitator');
        })->get();

        $pemohonList = User::whereHas('roles', function ($q) {
            $q->where('name', 'pemohon');
        })->whereHas('kabupatenKota', function ($q) use ($permohonan) {
            $q->where('id', $permohonan->kab_kota_id);
        })->get();

        // Generate nomor undangan
        $tahun = date('Y');
        $bulan = date('m');
        $lastUndangan = UndanganPelaksanaan::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();
        $nomorUrut = str_pad($lastUndangan + 1, 3, '0', STR_PAD_LEFT);
        $nomorUndangan = "UND-{$nomorUrut}/BPKAD/{$bulan}/{$tahun}";

        return view('undangan-pelaksanaan.create', compact('permohonan', 'verifikatorList', 'fasilitatorList', 'pemohonList', 'nomorUndangan'));
    }

    /**
     * Simpan undangan
     */
    public function store(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'nomor_undangan' => 'required|string|unique:undangan_pelaksanaan,nomor_undangan',
            'perihal' => 'required|string',
            'isi_undangan' => 'required|string',
            'file_undangan' => 'nullable|file|mimes:pdf|max:2048',
            'penerima' => 'required|array|min:1',
            'penerima.*' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('file_undangan')) {
                $filePath = $request->file('file_undangan')->store('undangan', 'public');
            }

            // Buat undangan
            $undangan = UndanganPelaksanaan::create([
                'permohonan_id' => $permohonan->id,
                'penetapan_jadwal_id' => $permohonan->penetapanJadwal->id,
                'nomor_undangan' => $request->nomor_undangan,
                'perihal' => $request->perihal,
                'isi_undangan' => $request->isi_undangan,
                'file_undangan' => $filePath,
                'status' => 'draft',
                'dibuat_oleh' => Auth::id(),
                'tanggal_dibuat' => now(),
            ]);

            // Tambahkan penerima
            foreach ($request->penerima as $userId) {
                $user = User::find($userId);
                $jenisPenerima = 'pemohon'; // default

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

            DB::commit();

            return redirect()->route('undangan-pelaksanaan.show', $permohonan)
                ->with('success', 'Undangan pelaksanaan berhasil dibuat.');
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

        return view('undangan-pelaksanaan.show', compact('permohonan', 'undangan'));
    }

    /**
     * Kirim undangan
     */
    public function send(Permohonan $permohonan)
    {
        $undangan = $permohonan->undanganPelaksanaan;

        if (!$undangan) {
            return back()->with('error', 'Undangan tidak ditemukan.');
        }

        if ($undangan->isTerkirim()) {
            return back()->with('info', 'Undangan sudah dikirim sebelumnya.');
        }

        try {
            DB::beginTransaction();

            $undangan->update([
                'status' => 'terkirim',
                'tanggal_dikirim' => now(),
            ]);

            // Update tahapan permohonan (Tahap 7: Persiapan Pelaksanaan = selesai)
            try {
                $tahapanPersiapan = \App\Models\MasterTahapan::where('nama_tahapan', 'Persiapan Pelaksanaan')->first();

                if ($tahapanPersiapan) {
                    \App\Models\PermohonanTahapan::updateOrCreate(
                        [
                            'permohonan_id' => $permohonan->id,
                            'tahapan_id' => $tahapanPersiapan->id
                        ],
                        [
                            'status' => 'selesai',
                            'tanggal_selesai' => now(),
                            'keterangan' => 'Undangan pelaksanaan telah dikirim'
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Gagal update tahapan persiapan: ' . $e->getMessage());
            }

            DB::commit();

            // TODO: Kirim notifikasi email/push notification ke penerima

            return back()->with('success', 'Undangan berhasil dikirim ke semua penerima.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim undangan: ' . $e->getMessage());
        }
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

        return view('undangan-pelaksanaan.my-undangan', compact('undanganList'));
    }

    /**
     * Lihat detail undangan (untuk penerima)
     */
    public function view($id)
    {
        $undanganPenerima = UndanganPenerima::with(['undangan.permohonan.kabupatenKota', 'undangan.penetapanJadwal'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Mark as read
        if (!$undanganPenerima->dibaca) {
            $undanganPenerima->markAsRead();
        }

        return view('undangan-pelaksanaan.view', compact('undanganPenerima'));
    }
}
