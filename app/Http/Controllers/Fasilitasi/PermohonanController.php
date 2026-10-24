<?php

namespace App\Http\Controllers\Fasilitasi;

use App\Http\Controllers\Controller;
use App\Models\DokumenTahapan;
use App\Models\JadwalFasilitasi;
use App\Models\MasterKelengkapanVerifikasi;
use App\Models\MasterTahapan;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanTahapan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use App\Services\PermohonanNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

/**
 * Permohonan fasilitasi: daftar, buat, ajukan, hapus, dan halaman per tahapan. Inti alur bisnis.
 *
 * Akses: lihat: PermohonanPolicy::view + scope visibleTo; buat/ajukan/hapus: permohonan.create + PermohonanPolicy::update; ubah batas waktu: tahapan.update-deadline.
 * View: resources/views/pages/fasilitasi
 */
class PermohonanController extends Controller
{
    /**
     * Daftar permohonan yang terlihat oleh pengguna (scope visibleTo), dengan pencarian dan filter.
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jadwalFasilitasi']);

        $query->visibleTo(Auth::user());

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tahun', 'like', '%'.$request->search.'%')
                    ->orWhere('jenis_dokumen', 'like', '%'.$request->search.'%')
                    ->orWhereHas('kabupatenKota', function ($qq) use ($request) {
                        $qq->where('nama', 'like', '%'.$request->search.'%');
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_akhir', $request->status);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $permohonan = $query->latest()->paginate(10);

        $filterOptions = [
            'tahunList' => Permohonan::distinct('tahun')->orderBy('tahun', 'desc')->pluck('tahun'),
            'statusOptions' => [
                'belum' => 'Belum Dimulai',
                'proses' => 'Dalam Proses',
                'revisi' => 'Perlu Revisi',
                'selesai' => 'Selesai',
            ],
        ];

        return view('pages.fasilitasi.index', compact('permohonan', 'filterOptions'));
    }

    /**
     * Form buat permohonan dari jadwal fasilitasi yang masih dibuka (atau diperpanjang).
     */
    public function create(Request $request)
    {
        $userId = Auth::id();

        // Jadwal yang published dan (masih terbuka ATAU pemohon punya perpanjangan waktu yang disetujui & masih berlaku)
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')
            ->where(function ($q) use ($userId) {
                $q->where('batas_permohonan', '>=', now())
                    ->orWhereHas('perpanjanganWaktu', function ($qq) use ($userId) {
                        $qq->where('user_id', $userId)
                            ->whereNotNull('diproses_at')
                            ->whereNotNull('batas_waktu')
                            ->where('batas_waktu', '>=', now());
                    });
            })
            ->with(['jenisDokumen'])
            ->get()
            ->each(function ($jadwal) use ($userId) {
                $jadwal->extension_deadline = $jadwal->batas_permohonan && $jadwal->batas_permohonan->isPast()
                    ? $jadwal->validExtensionFor($userId)?->batas_waktu
                    : null;
            });

        // Pre-select jadwal if jadwal_id provided
        $selectedJadwal = null;
        if ($request->filled('jadwal_id')) {
            $selectedJadwal = JadwalFasilitasi::find($request->jadwal_id);
        }

        return view('pages.fasilitasi.create', compact('jadwalFasilitasi', 'selectedJadwal'));
    }

    /**
     * Simpan permohonan baru beserta baris dokumen kelengkapan dan tahapan awal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
        ]);

        // Cek apakah jadwal masih aktif (atau pemohon punya perpanjangan waktu yang masih berlaku)
        $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
        $deadlineLewat = $jadwal->batas_permohonan && $jadwal->batas_permohonan->lt(now());
        if ($deadlineLewat && ! $jadwal->hasValidExtensionFor(Auth::id())) {
            return redirect()->back()->withErrors(['jadwal_fasilitasi_id' => 'Jadwal permohonan sudah ditutup.']);
        }

        // Cek apakah user sudah pernah membuat permohonan untuk jadwal ini
        $existingPermohonan = Permohonan::where('jadwal_fasilitasi_id', $request->jadwal_fasilitasi_id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingPermohonan) {
            return redirect()->route('permohonan.show', $existingPermohonan)
                ->with('info', 'Anda sudah memiliki permohonan untuk jadwal ini.');
        }

        // Buat permohonan dengan data dari jadwal
        $permohonan = Permohonan::create([
            'user_id' => Auth::id(),
            'kab_kota_id' => Auth::user()->kabupaten_kota_id,
            'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
            'tahun' => $jadwal->tahun_anggaran,
            'jenis_dokumen_id' => $jadwal->jenis_dokumen,
            'status_akhir' => 'belum',
        ]);

        // Buat record tahapan pertama (Permohonan) dengan status proses
        $tahapanPermohonan = MasterTahapan::where('urutan', 1)->first();
        if ($tahapanPermohonan) {
            PermohonanTahapan::create([
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $tahapanPermohonan->id,
                'status' => 'proses',
            ]);
        }

        // Auto-generate dokumen persyaratan sesuai jenis dokumen yang difasilitasi
        $kelengkapanList = MasterKelengkapanVerifikasi::where('jenis_dokumen_id', $permohonan->jenis_dokumen_id)
            ->orderBy('urutan')
            ->get();
        foreach ($kelengkapanList as $kelengkapan) {
            PermohonanDokumen::create([
                'permohonan_id' => $permohonan->id,
                'master_kelengkapan_id' => $kelengkapan->id,
                'is_ada' => false,
                'status_verifikasi' => 'pending',
            ]);
        }

        // Log activity
        activity()
            ->performedOn($permohonan)
            ->causedBy(Auth::user())
            ->withProperties([
                'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? '-',
                'tahun' => $permohonan->tahun,
                'jenis_dokumen' => $permohonan->jenisDokumen->nama ?? '-',
            ])
            ->log('Permohonan fasilitasi dibuat oleh '.Auth::user()->name);

        // Kirim notifikasi ke admin
        $admins = User::role(['admin_peran', 'kaban', 'superadmin'])->get();
        foreach ($admins as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'title' => 'Permohonan Baru Dibuat',
                'message' => 'Permohonan fasilitasi baru dari '.($permohonan->kabupatenKota->nama ?? '-').' untuk tahun '.$permohonan->tahun.' telah dibuat dan sedang dalam tahap pengisian dokumen.',
                'type' => 'info',
                'model_type' => Permohonan::class,
                'model_id' => $permohonan->id,
                'action_url' => route('permohonan.show', $permohonan),
                'is_read' => false,
            ]);
        }

        return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dibuat. Silakan lengkapi dokumen persyaratan.');
    }

    /**
     * Detail permohonan dengan progress tracker.
     */
    public function show(Permohonan $permohonan)
    {
        // Cek hak akses
        $this->authorizeView($permohonan);

        // Load relasi untuk tampilan lengkap
        $permohonan->load([
            'kabupatenKota',
            'jadwalFasilitasi',
            'jenisDokumen',
            'permohonanDokumen.masterKelengkapan',
            'perpanjanganWaktu',
            'undanganPelaksanaan',
            'hasilFasilitasi',
            'tindakLanjut',
            'activityLogs.causer',
        ]);

        return view('pages.fasilitasi.show', compact('permohonan'));
    }

    /**
     * Ajukan permohonan: wajib semua dokumen kelengkapan ada. Status menjadi "proses", tahapan Verifikasi dimulai, dan notifikasi dikirim.
     */
    public function submit(Request $request, Permohonan $permohonan)
    {
        Gate::authorize('update', $permohonan);

        // Allow resubmit if status is 'proses' but submitted_at is null (failed submission)
        if ($permohonan->status_akhir !== 'belum' && ! ($permohonan->status_akhir === 'proses' && is_null($permohonan->submitted_at))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permohonan sudah dikirim sebelumnya.',
                ], 400);
            }

            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim sebelumnya.');
        }

        // Validasi: Cek apakah semua dokumen wajib sudah diupload
        $dokumenBelumLengkap = PermohonanDokumen::where('permohonan_id', $permohonan->id)
            ->where('is_ada', false)
            ->exists();

        if ($dokumenBelumLengkap) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengirim permohonan. Harap lengkapi semua dokumen persyaratan terlebih dahulu.',
                ], 400);
            }

            return redirect()->route('permohonan.show', $permohonan)
                ->with('error', 'Tidak dapat mengirim permohonan. Harap lengkapi semua dokumen persyaratan terlebih dahulu.');
        }

        try {
            DB::beginTransaction();

            // Load relationships yang diperlukan
            $permohonan->load(['kabupatenKota', 'jenisDokumen']);

            // Update status ke proses (tahapan permohonan)
            // Status hanya akan menjadi 'selesai' ketika submit di tahapan penetapan perda (tahapan terakhir)
            $permohonan->status_akhir = 'proses';
            $permohonan->submitted_at = now();
            $permohonan->save();
            $permohonan->refresh();

            // Update tahapan Permohonan menjadi selesai
            $masterTahapanPermohonan = MasterTahapan::where('nama_tahapan', 'Permohonan')->first();
            if ($masterTahapanPermohonan) {
                PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanPermohonan->id,
                    ],
                    [
                        'status' => 'selesai',
                        'catatan' => 'Permohonan berhasil diajukan dengan semua dokumen lengkap',
                        'updated_by' => Auth::id(),
                    ]
                );
            }

            // Buat tahapan Verifikasi (tahapan berikutnya dimulai)
            $masterTahapanVerifikasi = MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
            if ($masterTahapanVerifikasi) {
                PermohonanTahapan::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'tahapan_id' => $masterTahapanVerifikasi->id,
                    ],
                    [
                        'status' => 'proses',
                        'catatan' => 'Menunggu verifikasi dokumen oleh verifikator',
                        'updated_by' => Auth::id(),
                    ]
                );
            }

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? '-',
                    'tahun' => $permohonan->tahun,
                    'jenis_dokumen' => $permohonan->jenisDokumen->nama ?? '-',
                    'status_lama' => 'belum',
                    'status_baru' => 'proses',
                ])
                ->log('Permohonan fasilitasi diajukan oleh '.Auth::user()->name.' dan menunggu verifikasi');

            // Kirim notifikasi (database + WhatsApp) ke Admin dan Tim Fedora
            $notificationService = app(PermohonanNotificationService::class);
            $notificationService->notifyPermohonanSubmitted($permohonan);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.',
                ]);
            }

            return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error submit permohonan: '.$e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim permohonan. Silakan coba lagi.',
                ], 500);
            }

            return redirect()->route('permohonan.tahapan.permohonan', $permohonan)
                ->with('error', 'Terjadi kesalahan saat mengirim permohonan. Silakan coba lagi.');
        }
    }

    /**
     * Hapus permohonan yang belum diajukan (hanya akun pembuatnya).
     */
    public function destroy(Permohonan $permohonan)
    {
        Gate::authorize('update', $permohonan);

        // Hanya bisa hapus kalo status belum
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dalam proses dan tidak bisa dihapus.');
        }

        $permohonan->delete();

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dihapus.');
    }

    // ============================================================
    // TAHAPAN DETAIL METHODS
    // ============================================================

    /**
     * Halaman tahapan 1: dokumen kelengkapan.
     */
    public function tahapanPermohonan(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'permohonanDokumen.verifiedBy',
        ]);

        $permohonan->setRelation(
            'permohonanDokumen',
            $permohonan->permohonanDokumen()
                ->with('masterKelengkapan')
                ->join('master_kelengkapan_verifikasi', 'permohonan_dokumen.master_kelengkapan_id', '=', 'master_kelengkapan_verifikasi.id')
                ->orderBy('master_kelengkapan_verifikasi.urutan')
                ->select('permohonan_dokumen.*')
                ->get()
        );
        // dd($permohonan);

        return view('pages.fasilitasi.tahapan.permohonan', compact('permohonan'));
    }

    /**
     * Halaman tahapan 2: hasil verifikasi dan laporan verifikasi.
     */
    public function tahapanVerifikasi(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'permohonanDokumen.masterKelengkapan',
            'permohonanDokumen.verifiedBy',
            'perpanjanganWaktu',
        ]);

        return view('pages.fasilitasi.tahapan.verifikasi', compact('permohonan'));
    }

    /**
     * Halaman tahapan 3: penetapan jadwal dan undangan pelaksanaan.
     */
    public function tahapanJadwal(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'penetapanJadwal',
        ]);

        return view('pages.fasilitasi.tahapan.jadwal', compact('permohonan'));
    }

    /**
     * Halaman tahapan 4: dokumen pelaksanaan.
     */
    public function tahapanPelaksanaan(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        return view('pages.fasilitasi.tahapan.pelaksanaan', compact('permohonan'));
    }

    /**
     * Halaman tahapan 5: hasil fasilitasi dan draft final.
     */
    public function tahapanHasil(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'hasilFasilitasi.hasilSistematika.masterBab',
            'hasilFasilitasi.hasilUrusan.masterUrusan',
            'hasilFasilitasi.pembuat',
        ]);

        return view('pages.fasilitasi.tahapan.hasil', compact('permohonan'));
    }

    /**
     * Halaman tahapan 6: dokumen tindak lanjut.
     */
    public function tahapanTindakLanjut(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'tindakLanjut',
        ]);

        // Get dokumen tindak lanjut dari DokumenTahapan
        $masterTahapan = MasterTahapan::where('id', 6)->first();
        $dokumenTindakLanjut = null;

        if ($masterTahapan) {
            $dokumenTindakLanjut = DokumenTahapan::where('permohonan_id', $permohonan->id)
                ->where('tahapan_id', $masterTahapan->id)
                ->where('permohonan_id', $permohonan->id)
                ->latest()
                ->first();
        }

        return view('pages.fasilitasi.tahapan.tindak-lanjut', compact('permohonan', 'dokumenTindakLanjut'));
    }

    /**
     * Halaman tahapan 7: dokumen penetapan PERDA/PERKADA.
     */
    public function tahapanPenetapan(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);

        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'penetapanPerda',
        ]);

        // Get dokumen penetapan perda dari DokumenTahapan
        $masterTahapan = MasterTahapan::where('id', 7)->first();
        $dokumenPenetapan = null;

        if ($masterTahapan) {
            $dokumenPenetapan = DokumenTahapan::where('permohonan_id', $permohonan->id)
                ->where('tahapan_id', $masterTahapan->id)
                ->latest()
                ->first();
        }

        return view('pages.fasilitasi.tahapan.penetapan', compact('permohonan', 'dokumenPenetapan'));
    }

    /**
     * Ubah batas waktu suatu tahapan permohonan (dicatat di activity log dan dinotifikasikan).
     */
    public function updateDeadline(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'deadline' => 'required|date|after:now',
            'keterangan' => 'required|string|min:20',
            'tahapan' => 'required|string',
        ], [
            'deadline.required' => 'Deadline wajib diisi',
            'deadline.after' => 'Deadline harus lebih dari waktu sekarang',
            'keterangan.required' => 'Keterangan wajib diisi',
            'keterangan.min' => 'Keterangan minimal 20 karakter',
        ]);

        try {
            DB::beginTransaction();

            // Get tahapan berdasarkan nama
            $masterTahapan = MasterTahapan::where('nama_tahapan', $request->tahapan)->first();

            if (! $masterTahapan) {
                return redirect()->back()->with('error', 'Tahapan tidak ditemukan.');
            }

            // Get atau create permohonan tahapan
            $permohonanTahapan = PermohonanTahapan::firstOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapan->id,
                ],
                [
                    'status' => 'proses',
                    'updated_by' => Auth::id(),
                ]
            );

            $deadlineLama = $permohonanTahapan->deadline;

            // Update deadline
            $permohonanTahapan->update([
                'deadline' => $request->deadline,
                'updated_by' => Auth::id(),
            ]);

            // Log activity
            activity()
                ->performedOn($permohonan)
                ->causedBy(Auth::user())
                ->withProperties([
                    'tahapan' => $request->tahapan,
                    'deadline_lama' => $deadlineLama ? Carbon::parse($deadlineLama)->format('d F Y, H:i') : 'Belum ada',
                    'deadline_baru' => Carbon::parse($request->deadline)->format('d F Y, H:i'),
                    'keterangan' => $request->keterangan,
                ])
                ->log('Deadline tahapan '.$request->tahapan.' diperbarui oleh '.Auth::user()->name);

            // Kirim notifikasi ke fasilitator yang terkait
            $fasilitators = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where('role_type', 'fasilitator')
                ->with('user')
                ->get();

            foreach ($fasilitators as $assignment) {
                if ($assignment->user) {
                    Notifikasi::create([
                        'user_id' => $assignment->user->id,
                        'title' => 'Deadline Diperbarui',
                        'message' => 'Deadline untuk tahapan '.$request->tahapan.' permohonan '.
                                   ($permohonan->kabupatenKota->nama ?? '-').' telah diperbarui menjadi '.
                                   Carbon::parse($request->deadline)->format('d F Y, H:i').'. '.
                                   'Keterangan: '.$request->keterangan,
                        'type' => 'info',
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => route('permohonan.tahapan.hasil', $permohonan),
                        'is_read' => false,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Deadline berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error update deadline: '.$e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui deadline.');
        }
    }

    private function authorizeView(Permohonan $permohonan): void
    {
        Gate::authorize('view', $permohonan);
    }
}
