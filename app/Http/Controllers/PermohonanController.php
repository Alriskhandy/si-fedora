<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use App\Models\JadwalFasilitasi;
use App\Models\PermohonanDokumen;
use App\Models\MasterKelengkapanVerifikasi;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class PermohonanController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jadwalFasilitasi']);

        // Filter berdasarkan role
        if (Auth::user()->hasRole('pemohon')) {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->hasRole('verifikator')) {
            // Verifikator lewat UserKabkotaAssignment
            $assignments = \App\Models\UserKabkotaAssignment::where('user_id', Auth::id())
                ->where('is_active', true)
                ->get();

            if ($assignments->isNotEmpty()) {
                $query->where(function ($q) use ($assignments) {
                    foreach ($assignments as $assignment) {
                        $q->orWhere(function ($qq) use ($assignment) {
                            $qq->where('kab_kota_id', $assignment->kabupaten_kota_id)
                                ->where('tahun', $assignment->tahun);

                            // Filter by jenis dokumen jika ada
                            if ($assignment->jenis_dokumen_id) {
                                $qq->where('jenis_dokumen_id', $assignment->jenis_dokumen_id);
                            }
                        });
                    }
                });
                // Hanya tampilkan permohonan yang sudah disubmit
                $query->whereIn('status_akhir', ['proses', 'revisi', 'selesai']);
            } else {
                // Jika tidak ada assignment, tampilkan hasil kosong
                $query->whereRaw('1 = 0');
            }
        } elseif (Auth::user()->hasRole('fasilitator')) {
            // Fasilitator lewat UserKabkotaAssignment
            $assignments = \App\Models\UserKabkotaAssignment::where('user_id', Auth::id())
                ->where('is_active', true)
                ->get();

            if ($assignments->isNotEmpty()) {
                $query->where(function ($q) use ($assignments) {
                    foreach ($assignments as $assignment) {
                        $q->orWhere(function ($qq) use ($assignment) {
                            $qq->where('kab_kota_id', $assignment->kabupaten_kota_id)
                                ->where('tahun', $assignment->tahun);

                            // Filter by jenis dokumen jika ada
                            if ($assignment->jenis_dokumen_id) {
                                $qq->where('jenis_dokumen_id', $assignment->jenis_dokumen_id);
                            }
                        });
                    }
                });
                // Fasilitator lihat permohonan yang sudah terverifikasi atau lebih
                $query->whereIn('status_akhir', ['proses', 'revisi', 'selesai']);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif (Auth::user()->hasAnyRole(['admin_peran', 'kaban', 'superadmin', 'auditor'])) {
            // Admin, Kaban, Superadmin, dan Auditor bisa lihat semua permohonan
            // No filter needed
        } else {
            // Role lain tidak bisa akses
            $query->whereRaw('1 = 0');
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tahun', 'like', '%' . $request->search . '%')
                    ->orWhere('jenis_dokumen', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kabupatenKota', function ($qq) use ($request) {
                        $qq->where('nama', 'like', '%' . $request->search . '%');
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
            ]
        ];

        return view('pages.fasilitasi.index', compact('permohonan', 'filterOptions'));
    }

    public function create(Request $request)
    {
        // Hanya jadwal yang published yang bisa dipilih
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')
            ->where('batas_permohonan', '>=', now())->with(['jenisDokumen'])
            ->get();

        // Pre-select jadwal if jadwal_id provided
        $selectedJadwal = null;
        if ($request->filled('jadwal_id')) {
            $selectedJadwal = JadwalFasilitasi::find($request->jadwal_id);
        }

        return view('pages.fasilitasi.create', compact('jadwalFasilitasi', 'selectedJadwal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
        ]);

        // Cek apakah jadwal masih aktif
        $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
        if ($jadwal->batas_permohonan < now()) {
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
        $tahapanPermohonan = \App\Models\MasterTahapan::where('urutan', 1)->first();
        if ($tahapanPermohonan) {
            \App\Models\PermohonanTahapan::create([
                'permohonan_id' => $permohonan->id,
                'tahapan_id' => $tahapanPermohonan->id,
                'status' => 'proses',
            ]);
        }

        // Auto-generate dokumen persyaratan berdasarkan master_kelengkapan_verifikasi
        $kelengkapanList = MasterKelengkapanVerifikasi::orderBy('urutan')->get();
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
            ->log('Permohonan fasilitasi dibuat oleh ' . Auth::user()->name);

        // Kirim notifikasi ke admin
        $admins = User::role(['admin_peran', 'kaban', 'superadmin'])->get();
        foreach ($admins as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'title' => 'Permohonan Baru Dibuat',
                'message' => 'Permohonan fasilitasi baru dari ' . ($permohonan->kabupatenKota->nama ?? '-') . ' untuk tahun ' . $permohonan->tahun . ' telah dibuat dan sedang dalam tahap pengisian dokumen.',
                'type' => 'info',
                'model_type' => Permohonan::class,
                'model_id' => $permohonan->id,
                'action_url' => route('permohonan.show', $permohonan),
                'is_read' => false,
            ]);
        }

        return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dibuat. Silakan lengkapi dokumen persyaratan.');
    }

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
            'penetapanPerda',
            'activityLogs.causer'
        ]);

        return view('pages.fasilitasi.show', compact('permohonan'));
    }

    /**
     * Show permohonan with tab-based layout (alternate view for testing)
     */
    public function showWithTabs(Permohonan $permohonan)
    {
        // Cek hak akses
        $this->authorizeView($permohonan);

        // Load relasi untuk tampilan lengkap
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'penetapanJadwal',
            'koordinator.koordinator',
            'permohonanDokumen.masterKelengkapan',
            'perpanjanganWaktu',
            'undanganPelaksanaan',
            'hasilFasilitasi',
            'tindakLanjut',
            'penetapanPerda',
            'activityLogs.causer'
        ]);

        return view('pages.fasilitasi.show', compact('permohonan'));
    }

    public function edit(Permohonan $permohonan)
    {
        // Hanya bisa edit kalo status belum
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dalam proses dan tidak bisa diedit.');
        }

        // Cek hak akses
        $this->authorizeView($permohonan);

        return view('permohonan.edit', compact('permohonan'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        // Hanya bisa edit kalo status belum
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dalam proses dan tidak bisa diedit.');
        }

        // Update logic here (for now just redirect)
        return redirect()->route('permohonan.edit', $permohonan)->with('success', 'Permohonan berhasil diperbarui.');
    }

    public function submit(Request $request, Permohonan $permohonan)
    {
        // Allow resubmit if status is 'proses' but submitted_at is null (failed submission)
        if ($permohonan->status_akhir !== 'belum' && !($permohonan->status_akhir === 'proses' && is_null($permohonan->submitted_at))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permohonan sudah dikirim sebelumnya.'
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
                    'message' => 'Tidak dapat mengirim permohonan. Harap lengkapi semua dokumen persyaratan terlebih dahulu.'
                ], 400);
            }
            return redirect()->route('permohonan.show', $permohonan)
                ->with('error', 'Tidak dapat mengirim permohonan. Harap lengkapi semua dokumen persyaratan terlebih dahulu.');
        }

        try {
            DB::beginTransaction();

            // Load relationships yang diperlukan
            $permohonan->load(['kabupatenKota', 'jenisDokumen']);

            // Update status ke proses
            $permohonan->status_akhir = 'proses';
            $permohonan->submitted_at = now();
            $permohonan->save();
            $permohonan->refresh();

            // Update tahapan Permohonan menjadi selesai
            $masterTahapanPermohonan = \App\Models\MasterTahapan::where('nama_tahapan', 'Permohonan')->first();
            if ($masterTahapanPermohonan) {
                \App\Models\PermohonanTahapan::updateOrCreate(
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
            $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
            if ($masterTahapanVerifikasi) {
                \App\Models\PermohonanTahapan::updateOrCreate(
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
                ->log('Permohonan fasilitasi diajukan oleh ' . Auth::user()->name . ' dan menunggu verifikasi');

            // Kirim notifikasi ke verifikator yang ditugaskan
            $verifikators = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->with('user')
                ->get();

            foreach ($verifikators as $assignment) {
                if ($assignment->user && $assignment->user->hasRole('verifikator')) {
                    Notifikasi::create([
                        'user_id' => $assignment->user_id,
                        'title' => 'Permohonan Baru Perlu Diverifikasi',
                        'message' => 'Permohonan fasilitasi dari ' . ($permohonan->kabupatenKota->nama ?? '-') . ' untuk tahun ' . $permohonan->tahun . ' memerlukan verifikasi Anda.',
                        'type' => 'info',
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => route('permohonan.show', $permohonan),
                        'is_read' => false,
                    ]);
                }
            }

            // Kirim notifikasi ke admin
            $admins = User::role(['admin_peran', 'kaban', 'superadmin'])->get();
            foreach ($admins as $admin) {
                Notifikasi::create([
                    'user_id' => $admin->id,
                    'title' => 'Permohonan Baru Diajukan',
                    'message' => 'Permohonan fasilitasi baru dari ' . ($permohonan->kabupatenKota->nama ?? '-') . ' untuk tahun ' . $permohonan->tahun . ' telah diajukan.',
                    'type' => 'info',
                    'model_type' => Permohonan::class,
                    'model_id' => $permohonan->id,
                    'action_url' => route('permohonan.show', $permohonan),
                    'is_read' => false,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.'
                ]);
            }

            return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error submit permohonan: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim permohonan. Silakan coba lagi.'
                ], 500);
            }

            return redirect()->route('permohonan.tahapan.permohonan', $permohonan)
                ->with('error', 'Terjadi kesalahan saat mengirim permohonan. Silakan coba lagi.');
        }
    }

    public function destroy(Permohonan $permohonan)
    {
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

    public function tahapanPermohonan(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'createdBy',
            'permohonanDokumen.masterKelengkapan',
            'permohonanDokumen.verifiedBy'
        ]);

        return view('pages.fasilitasi.tahapan.permohonan', compact('permohonan'));
    }

    public function tahapanVerifikasi(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'permohonanDokumen.masterKelengkapan',
            'permohonanDokumen.verifiedBy',
            'perpanjanganWaktu'
        ]);

        return view('pages.fasilitasi.tahapan.verifikasi', compact('permohonan'));
    }

    public function tahapanJadwal(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'penetapanJadwal',
            'koordinator.koordinator'
        ]);

        return view('pages.fasilitasi.tahapan.jadwal', compact('permohonan'));
    }

    public function tahapanPelaksanaan(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'undanganPelaksanaan',
            'dokumentasiPelaksanaan'
        ]);

        return view('pages.fasilitasi.tahapan.pelaksanaan', compact('permohonan'));
    }

    public function tahapanHasil(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'hasilFasilitasi.hasilSistematika.bab',
            'hasilFasilitasi.hasilUrusan.urusan',
            'hasilFasilitasi.fasilitator'
        ]);

        return view('pages.fasilitasi.tahapan.hasil', compact('permohonan'));
    }

    public function tahapanTindakLanjut(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'tindakLanjut',
            'suratPenyampaianHasil'
        ]);

        return view('pages.fasilitasi.tahapan.tindak-lanjut', compact('permohonan'));
    }

    public function tahapanPenetapan(Permohonan $permohonan)
    {
        $this->authorizeView($permohonan);
        
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'jadwalFasilitasi',
            'penetapanPerda'
        ]);

        return view('pages.fasilitasi.tahapan.penetapan', compact('permohonan'));
    }

    private function authorizeView(Permohonan $permohonan)
    {
        $user = Auth::user();

        // Pemohon (Kabupaten/Kota) hanya bisa lihat permohonan miliknya sendiri
        if ($user->hasRole('pemohon')) {
            if ($permohonan->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Verifikator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('verifikator')) {
            $hasAccess = \App\Models\UserKabkotaAssignment::where('user_id', $user->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Fasilitator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('fasilitator')) {
            $hasAccess = \App\Models\UserKabkotaAssignment::where('user_id', $user->id)
                ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->where(function ($q) use ($permohonan) {
                    $q->whereNull('jenis_dokumen_id')
                        ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
                })
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Admin, Kaban, Superadmin, dan Auditor bisa lihat semua permohonan
        elseif ($user->hasAnyRole(['admin_peran', 'kaban', 'superadmin', 'auditor'])) {
            // Full access - no restriction
            return;
        }
        // Role lain tidak memiliki akses
        else {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }
    }
}
