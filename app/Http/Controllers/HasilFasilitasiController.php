<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiUrusan;
use App\Models\HasilFasilitasiSistematika;
use App\Models\MasterUrusan;
use App\Models\MasterBab;
use App\Models\MasterJenisDokumen;
use App\Models\UserKabkotaAssignment;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\HasilFasilitasiDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * Controller untuk mengelola hasil fasilitasi/evaluasi
 * 
 * Fitur utama:
 * - CRUD hasil fasilitasi (index, create, store, show, submit)
 * - Manajemen sistematika (storeSistematika, deleteSistematika)
 * - Manajemen urusan pemerintahan (storeUrusan, deleteUrusan)
 * - Generate & download dokumen (generate, downloadWord, downloadPdf, previewPdf)
 * - Upload & submit draft final ke Kepala Badan (uploadDraftFinal, downloadDraftFinal, submitToKaban)
 * - Activity logging untuk audit trail
 * - Real-time notifications ke anggota tim
 */
class HasilFasilitasiController extends Controller
{
    protected $documentService;

    public function __construct(HasilFasilitasiDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    // ============================================================
    // AUTHORIZATION HELPER METHODS
    // ============================================================

    /**
     * Check if user is koordinator for this permohonan
     * 
     * @param Permohonan $permohonan
     * @return bool
     */
    private function isKoordinator(Permohonan $permohonan): bool
    {
        // Get jenis_dokumen_id directly from permohonan
        $jenisDokumenId = $permohonan->jenis_dokumen_id;

        // Get assignment for debugging
        $assignment = UserKabkotaAssignment::where('user_id', Auth::id())
            ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('tahun', $permohonan->tahun)
            ->where('role_type', 'fasilitator')
            ->where('is_pic', true)
            ->where('is_active', true)
            ->first();

        // Check if user is fasilitator with is_pic = true (koordinator) for this specific team
        $isKoord = $assignment !== null;

        Log::info('isKoordinator Check', [
            'user_id' => Auth::id(),
            'permohonan_id' => $permohonan->id,
            'kab_kota_id' => $permohonan->kab_kota_id,
            'jenis_dokumen_id' => $jenisDokumenId,
            'tahun' => $permohonan->tahun,
            'assignment_found' => $assignment ? 'YES' : 'NO',
            'result' => $isKoord
        ]);

        return $isKoord;
    }

    /**
     * Check if user is admin or superadmin
     * 
     * @return bool
     */
    private function isAdmin(): bool
    {
        return Auth::user()->hasAnyRole(['admin_peran', 'superadmin']);
    }

    /**
     * Check if user is Kepala Badan
     * 
     * @return bool
     */
    private function isKepalaBadan(): bool
    {
        return Auth::user()->hasRole('kaban');
    }

    /**
     * Check if user is member of this permohonan's tim (fasilitator or verifikator)
     * 
     * @param Permohonan $permohonan
     * @return bool
     */
    private function isTimMember(Permohonan $permohonan): bool
    {
        // Admin can access all
        if ($this->isAdmin()) {
            return true;
        }

        // Get jenis_dokumen_id directly from permohonan
        $jenisDokumenId = $permohonan->jenis_dokumen_id;

        // Get assignment for debugging
        $assignment = UserKabkotaAssignment::where('user_id', Auth::id())
            ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('tahun', $permohonan->tahun)
            ->whereIn('role_type', ['fasilitator', 'verifikator'])
            ->where('is_active', true)
            ->first();

        $isMember = $assignment !== null;

        Log::info('isTimMember Check', [
            'user_id' => Auth::id(),
            'permohonan_id' => $permohonan->id,
            'kab_kota_id' => $permohonan->kab_kota_id,
            'jenis_dokumen_id' => $jenisDokumenId,
            'tahun' => $permohonan->tahun,
            'assignment_found' => $assignment ? 'YES (role: ' . $assignment->role_type . ')' : 'NO',
            'result' => $isMember
        ]);

        // Check if user is member of this specific team
        return $isMember;
    }

    /**
     * Check if user can edit/delete an item (owner or koordinator)
     * 
     * @param mixed $item
     * @param Permohonan $permohonan
     * @return bool
     */
    private function canManageItem($item, Permohonan $permohonan): bool
    {
        return $item->user_id == Auth::id() || $this->isKoordinator($permohonan);
    }

    /**
     * Check if user is verifikator for this permohonan
     * 
     * @param Permohonan $permohonan
     * @return bool
     */
    private function isVerifikator(Permohonan $permohonan): bool
    {
        $jenisDokumenId = $permohonan->jenis_dokumen_id;

        $assignment = UserKabkotaAssignment::where('user_id', Auth::id())
            ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('tahun', $permohonan->tahun)
            ->where('role_type', 'verifikator')
            ->where('is_active', true)
            ->first();

        return $assignment !== null;
    }

    // ============================================================
    // NOTIFICATION HELPER METHODS
    // ============================================================

    /**
     * Kirim notifikasi ke semua anggota tim
     * 
     * @param Permohonan $permohonan
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    private function notifyTeamMembers(Permohonan $permohonan, string $title, string $message, string $type = 'info'): void
    {
        $kabkotaId = $permohonan->kab_kota_id;
        $jenisDokumenId = $permohonan->jenis_dokumen_id;

        // Get all team members (fasilitator dan verifikator)
        $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $kabkotaId)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->whereIn('role_type', ['fasilitator', 'verifikator'])
            ->get();

        foreach ($assignments as $assignment) {
            // Skip current user
            if ($assignment->user_id != Auth::id()) {
                Notifikasi::create([
                    'user_id' => $assignment->user_id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'model_type' => HasilFasilitasi::class,
                    'model_id' => $permohonan->hasilFasilitasi?->id,
                    'action_url' => route('hasil-fasilitasi.show', $permohonan->id),
                    'is_read' => false,
                ]);
            }
        }
    }

    /**
     * Kirim notifikasi ke koordinator
     * 
     * @param Permohonan $permohonan
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    private function notifyKoordinator(Permohonan $permohonan, string $title, string $message, string $type = 'info'): void
    {
        $kabkotaId = $permohonan->kab_kota_id;
        $jenisDokumenId = $permohonan->jenis_dokumen_id;

        // Get koordinator (fasilitator dengan is_pic = true)
        $koordinator = UserKabkotaAssignment::where('kabupaten_kota_id', $kabkotaId)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->where('role_type', 'fasilitator')
            ->where('is_pic', true)
            ->first();

        if ($koordinator && $koordinator->user_id != Auth::id()) {
            Notifikasi::create([
                'user_id' => $koordinator->user_id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'model_type' => HasilFasilitasi::class,
                'model_id' => $permohonan->hasilFasilitasi?->id,
                'action_url' => route('hasil-fasilitasi.show', $permohonan->id),
                'is_read' => false,
            ]);
        }
    }

    /**
     * Kirim notifikasi ke admin dan kepala badan
     * 
     * @param Permohonan $permohonan
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    private function notifyAdminAndKaban(Permohonan $permohonan, string $title, string $message, string $type = 'info'): void
    {
        // Get all admin and superadmin users
        $admins = User::role(['admin_peran', 'superadmin', 'kaban'])->get();

        foreach ($admins as $admin) {
            if ($admin->id != Auth::id()) {
                Notifikasi::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'model_type' => HasilFasilitasi::class,
                    'model_id' => $permohonan->hasilFasilitasi?->id,
                    'action_url' => route('hasil-fasilitasi.show', $permohonan->id),
                    'is_read' => false,
                ]);
            }
        }
    }

    // ============================================================
    // MAIN CRUD OPERATIONS
    // ============================================================

    /**
     * Tampilkan daftar permohonan untuk input hasil fasilitasi
     * Menampilkan permohonan yang dapat diakses oleh user (fasilitator, verifikator, admin)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jenisDokumen', 'hasilFasilitasi']);

        // Admin can see all permohonan
        if (!$this->isAdmin()) {
            // Get user's tim assignments (fasilitator or verifikator)
            $userAssignments = UserKabkotaAssignment::where('user_id', Auth::id())
                ->whereIn('role_type', ['fasilitator', 'verifikator'])
                ->where('is_active', true)
                ->get();

            $query->where(function ($q) use ($userAssignments) {
                foreach ($userAssignments as $assignment) {
                    $q->orWhere(function ($subQ) use ($assignment) {
                        // Use correct column name: kab_kota_id (not kabupaten_kota_id)
                        $subQ->where('permohonan.kab_kota_id', $assignment->kabupaten_kota_id)
                            ->where('permohonan.tahun', $assignment->tahun)
                            ->where('permohonan.jenis_dokumen_id', $assignment->jenis_dokumen_id);
                    });
                }
            });
        }

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

        return view('pages.hasil-fasilitasi.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk membuat/edit hasil fasilitasi
     * Hanya dapat diakses oleh fasilitator/koordinator dan admin
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Permohonan $permohonan)
    {
        // Prevent verifikator from accessing create/edit (only fasilitator allowed)
        if ($this->isVerifikator($permohonan)) {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;
            if ($hasilFasilitasi) {
                return redirect()->route('hasil-fasilitasi.show', $permohonan)
                    ->with('info', 'Verifikator hanya dapat melihat hasil fasilitasi.');
            } else {
                return redirect()->route('hasil-fasilitasi.index')
                    ->with('info', 'Hasil fasilitasi belum dibuat. Verifikator tidak dapat membuat hasil fasilitasi.');
            }
        }

        // Check if user is member of this tim (fasilitator)
        if (!$this->isTimMember($permohonan)) {
            abort(403, 'Anda bukan anggota tim untuk permohonan ini.');
        }

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

        // Ambil daftar bab berdasarkan jenis dokumen dari permohonan (hanya parent/level 1)
        $masterBabList = collect();
        if ($permohonan->jenis_dokumen_id) {
            $masterBabList = MasterBab::where('jenis_dokumen_id', $permohonan->jenis_dokumen_id)
                ->whereNull('parent_id')
                ->orderBy('urutan')
                ->get();
        }

        // Load hasil fasilitasi dengan relasi
        $hasilFasilitasi = $permohonan->hasilFasilitasi;
        $hasilFasilitasi->load('hasilSistematika.masterBab', 'hasilSistematika.user', 'hasilUrusan.masterUrusan', 'hasilUrusan.user');

        // Sort sistematika by bab urutan
        $sortedSistematika = $hasilFasilitasi->hasilSistematika->sortBy(function ($item) {
            return $item->masterBab->urutan ?? 999;
        });
        $hasilFasilitasi->setRelation('hasilSistematika', $sortedSistematika);

        // Sort urusan by master urusan urutan
        $sortedUrusan = $hasilFasilitasi->hasilUrusan->sortBy(function ($item) {
            return $item->masterUrusan->urutan ?? 999;
        });
        $hasilFasilitasi->setRelation('hasilUrusan', $sortedUrusan);

        // Check if current user is koordinator (fasilitator dengan is_pic=true)
        $isKoordinator = $this->isKoordinator($permohonan);
        
        // Check if current user is admin
        $isAdmin = $this->isAdmin();

        // Get tim info untuk ditampilkan
        $kabkotaId = $permohonan->kab_kota_id;
        $jenisDokumenId = $permohonan->jenis_dokumen_id;

        $timInfo = null;
        if ($jenisDokumenId && $kabkotaId) {
            $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $kabkotaId)
                ->where('jenis_dokumen_id', $jenisDokumenId)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->with('user')
                ->get();

            $timInfo = [
                'verifikator' => $assignments->where('role_type', 'verifikator')->where('is_pic', true)->first(),
                'koordinator' => $assignments->where('role_type', 'fasilitator')->where('is_pic', true)->first(),
                'anggota' => $assignments->where('role_type', 'fasilitator')->where('is_pic', false)->values()
            ];
        }

        // Debug log
        Log::info('HasilFasilitasi Create', [
            'user_id' => Auth::id(),
            'permohonan_id' => $permohonan->id,
            'isKoordinator' => $isKoordinator,
            'tim_found' => $timInfo !== null
        ]);
        // dd($permohonan);

        return view('pages.hasil-fasilitasi.create', compact('permohonan', 'masterUrusanList', 'masterBabList', 'hasilFasilitasi', 'isKoordinator', 'isAdmin', 'timInfo'));
    }

    /**
     * Simpan hasil fasilitasi baru atau update yang sudah ada
     * Hanya dapat diakses oleh fasilitator/koordinator
     * 
     * @param Request $request
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
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
     * Tampilkan detail hasil fasilitasi dengan semua sistematikas dan urusans
     * Dapat diakses oleh tim member, verifikator, dan admin
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\View\View
     */
    public function show(Permohonan $permohonan)
    {
        // Admin can access all
        $isAdmin = $this->isAdmin();
        $isVerifikator = $this->isVerifikator($permohonan);
        $isTimMember = $this->isTimMember($permohonan);

        if (!$isAdmin && !$isTimMember && !$isVerifikator) {
            abort(403, 'Anda tidak memiliki akses untuk melihat hasil fasilitasi ini.');
        }

        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi) {
            // Verifikator tidak bisa create, redirect ke index
            if ($isVerifikator && !$isTimMember) {
                return redirect()->route('hasil-fasilitasi.index')
                    ->with('info', 'Hasil fasilitasi belum dibuat.');
            }
            return redirect()->route('hasil-fasilitasi.create', $permohonan)
                ->with('info', 'Hasil fasilitasi belum dibuat.');
        }

        $hasilFasilitasi->load('hasilUrusan.masterUrusan', 'hasilUrusan.user', 'hasilSistematika.masterBab', 'hasilSistematika.user', 'pembuat');

        // Sort sistematika by bab urutan
        $sortedSistematika = $hasilFasilitasi->hasilSistematika->sortBy(function ($item) {
            return $item->masterBab->urutan ?? 999;
        });
        $hasilFasilitasi->setRelation('hasilSistematika', $sortedSistematika);

        // Sort urusan by master urusan urutan
        $sortedUrusan = $hasilFasilitasi->hasilUrusan->sortBy(function ($item) {
            return $item->masterUrusan->urutan ?? 999;
        });
        $hasilFasilitasi->setRelation('hasilUrusan', $sortedUrusan);

         // Get tim info untuk ditampilkan
        $kabkotaId = $permohonan->kab_kota_id;
        $jenisDokumenId = $permohonan->jenis_dokumen_id;
        $timInfo = null;
        if ($jenisDokumenId && $kabkotaId) {
            $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $kabkotaId)
                ->where('jenis_dokumen_id', $jenisDokumenId)
                ->where('tahun', $permohonan->tahun)
                ->where('is_active', true)
                ->with('user')
                ->get();

            $timInfo = [
                'verifikator' => $assignments->where('role_type', 'verifikator')->where('is_pic', true)->first(),
                'koordinator' => $assignments->where('role_type', 'fasilitator')->where('is_pic', true)->first(),
                'anggota' => $assignments->where('role_type', 'fasilitator')->where('is_pic', false)->values()
            ];
        }

        // Pass isVerifikator to view
        $isKoordinator = $this->isKoordinator($permohonan);
        
        return view('pages.hasil-fasilitasi.show', compact('permohonan', 'hasilFasilitasi', 'isVerifikator', 'isAdmin', 'isKoordinator', 'timInfo'));
    }

    /**
     * Ajukan hasil fasilitasi ke admin untuk review
     * Hanya dapat dilakukan oleh koordinator
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
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

    // ============================================================
    // SISTEMATIKA OPERATIONS
    // ============================================================

    /**
     * Simpan item sistematika baru
     * 
     * @param Request $request
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSistematika(Request $request, Permohonan $permohonan)
    {
        // Check if user is member of this tim
        if (!$this->isTimMember($permohonan)) {
            return response()->json(['error' => 'Anda bukan anggota tim untuk permohonan ini'], 403);
        }

        $request->validate([
            'master_bab_id' => 'required|exists:master_bab,id',
            'sub_bab' => 'nullable|string',
            'catatan_penyempurnaan' => 'required|string',
        ]);

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return response()->json(['error' => 'Hasil fasilitasi belum dibuat'], 400);
            }

            $sistematika = HasilFasilitasiSistematika::create([
                'hasil_fasilitasi_id' => $hasilFasilitasi->id,
                'master_bab_id' => $request->master_bab_id,
                'sub_bab' => $request->sub_bab,
                'catatan_penyempurnaan' => $request->catatan_penyempurnaan,
                'user_id' => Auth::id(),
            ]);

            // Load relasi untuk response
            $sistematika->load('masterBab', 'user');

            // Activity Log
            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('sistematika_added')
                ->withProperties([
                    'bab' => $sistematika->masterBab->nama_bab,
                    'sub_bab' => $sistematika->sub_bab,
                ])
                ->log('Menambahkan item sistematika: ' . $sistematika->masterBab->nama_bab);

            // Notifikasi ke koordinator
            $this->notifyKoordinator(
                $permohonan,
                'Item Sistematika Ditambahkan',
                Auth::user()->name . ' menambahkan item sistematika "' . $sistematika->masterBab->nama_bab . '" ke hasil fasilitasi ' . $permohonan->kabupatenKota->nama,
                'info'
            );

            // Convert to array with rendered rich text
            $data = [
                'id' => $sistematika->id,
                'master_bab_id' => $sistematika->master_bab_id,
                'sub_bab' => $sistematika->sub_bab,
                'user_id' => $sistematika->user_id,
                'catatan_penyempurnaan' => is_object($sistematika->catatan_penyempurnaan) && method_exists($sistematika->catatan_penyempurnaan, 'render')
                    ? $sistematika->catatan_penyempurnaan->render()
                    : $sistematika->catatan_penyempurnaan,
                'masterBab' => $sistematika->masterBab,
                'user' => $sistematika->user,
                'created_at' => $sistematika->created_at->format('d/m/Y H:i'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Item sistematika berhasil ditambahkan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus item sistematika
     * Hanya dapat dilakukan oleh pemilik item atau koordinator
     * 
     * @param Permohonan $permohonan
     * @param int $id ID dari HasilFasilitasiSistematika
     * @return \Illuminate\Http\JsonResponse
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

            // Check authorization: owner or koordinator
            if (!$this->canManageItem($sistematika, $permohonan)) {
                return response()->json(['error' => 'Anda tidak memiliki akses untuk menghapus item ini'], 403);
            }

            // Store info before delete for activity log
            $babNama = $sistematika->masterBab->nama_bab;
            $subBab = $sistematika->sub_bab;

            $sistematika->delete();

            // Activity Log
            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('sistematika_deleted')
                ->withProperties([
                    'bab' => $babNama,
                    'sub_bab' => $subBab,
                ])
                ->log('Menghapus item sistematika: ' . $babNama);

            return response()->json([
                'success' => true,
                'message' => 'Item sistematika berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // URUSAN OPERATIONS
    // ============================================================

    /**
     * Simpan item urusan baru
     * 
     * @param Request $request
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUrusan(Request $request, Permohonan $permohonan)
    {
        // Check if user is member of this tim
        if (!$this->isTimMember($permohonan)) {
            return response()->json(['error' => 'Anda bukan anggota tim untuk permohonan ini'], 403);
        }

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
                'user_id' => Auth::id(),
            ]);

            $urusan->load('masterUrusan', 'user');

            // Activity Log
            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('urusan_added')
                ->withProperties([
                    'urusan' => $urusan->masterUrusan->nama_urusan,
                ])
                ->log('Menambahkan item urusan: ' . $urusan->masterUrusan->nama_urusan);

            // Notifikasi ke koordinator
            $this->notifyKoordinator(
                $permohonan,
                'Item Urusan Ditambahkan',
                Auth::user()->name . ' menambahkan item urusan "' . $urusan->masterUrusan->nama_urusan . '" ke hasil fasilitasi ' . $permohonan->kabupatenKota->nama,
                'info'
            );

            // Convert to array with rendered rich text
            $data = [
                'id' => $urusan->id,
                'master_urusan_id' => $urusan->master_urusan_id,
                'user_id' => $urusan->user_id,
                'catatan_masukan' => is_object($urusan->catatan_masukan) && method_exists($urusan->catatan_masukan, 'render')
                    ? $urusan->catatan_masukan->render()
                    : $urusan->catatan_masukan,
                'masterUrusan' => $urusan->masterUrusan,
                'user' => $urusan->user,
                'created_at' => $urusan->created_at->format('d/m/Y H:i'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Item urusan berhasil ditambahkan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus item urusan
     * Hanya dapat dilakukan oleh pemilik item atau koordinator
     * 
     * @param Permohonan $permohonan
     * @param int $id ID dari HasilFasilitasiUrusan
     * @return \Illuminate\Http\JsonResponse
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

            // Check authorization: owner or koordinator
            if (!$this->canManageItem($urusan, $permohonan)) {
                return response()->json(['error' => 'Anda tidak memiliki akses untuk menghapus item ini'], 403);
            }

            // Store info before delete for activity log
            $urusanNama = $urusan->masterUrusan->nama_urusan;

            $urusan->delete();

            // Activity Log
            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('urusan_deleted')
                ->withProperties([
                    'urusan' => $urusanNama,
                ])
                ->log('Menghapus item urusan: ' . $urusanNama);

            return response()->json([
                'success' => true,
                'message' => 'Item urusan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // DOCUMENT GENERATION & DOWNLOAD OPERATIONS
    // ============================================================

    /**
     * Generate dokumen hasil fasilitasi dalam format Word & PDF (koordinator only)
     * Membuat draft document dan menyimpannya ke storage
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Only koordinator can generate draft
            if (!$this->isKoordinator($permohonan)) {
                return redirect()->back()->with('error', 'Hanya koordinator yang dapat membuat draft dokumen.');
            }

            $sistematika = $hasilFasilitasi->hasilSistematika()
                ->with('masterBab')
                ->orderBy('master_bab_id')
                ->orderBy('id')
                ->get();

            $urusan = $hasilFasilitasi->hasilUrusan()
                ->with('masterUrusan')
                ->join('master_urusan', 'hasil_fasilitasi_urusan.master_urusan_id', '=', 'master_urusan.id')
                ->orderBy('master_urusan.urutan')
                ->orderBy('hasil_fasilitasi_urusan.id')
                ->select('hasil_fasilitasi_urusan.*')
                ->get();

            // Generate document using service
            $content = $this->documentService->generateWordDocument($permohonan, $sistematika, $urusan);

            // Save Word document to file
            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.doc';
            $filepath = $this->documentService->saveDocument($content, $filename);

            // Generate and save PDF file with same base name
            $pdfFilename = str_replace('.doc', '.pdf', $filename);
            $pdf = PDF::loadHTML($content)->setPaper('a4', 'portrait');
            
            // Save PDF to storage
            $pdfPath = 'hasil-fasilitasi/' . $pdfFilename;
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($pdfPath, $pdfContent);

            // Update draft_file in hasil_fasilitasi
            $hasilFasilitasi->update([
                'draft_file' => $filepath,
                'updated_by' => Auth::id()
            ]);

            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('draft_created')
                ->withProperties([
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                    'jenis_dokumen' => $permohonan->jenisDokumen->nama,
                ])
                ->log('Draft dokumen hasil fasilitasi berhasil dibuat (Word & PDF)');

            // Notifikasi ke semua anggota tim
            $this->notifyTeamMembers(
                $permohonan,
                'Draft Dokumen Tersedia',
                'Draft dokumen hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' telah dibuat dan siap untuk diunduh.',
                'success'
            );

            return redirect()->back()->with('success', 'Draft dokumen hasil fasilitasi berhasil dibuat. Tim dapat mengunduh dokumen dalam format Word atau PDF.');
        } catch (\Exception $e) {
            Log::error('Error generating hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat draft dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen Word hasil fasilitasi
     * Dapat diakses oleh semua tim member dan admin
     * 
     * @param Permohonan $permohonan
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadWord(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Check access: must be team member, admin, or kepala badan
            if (!$this->isTimMember($permohonan) && !$this->isAdmin() && !$this->isKepalaBadan()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk dokumen ini');
            }

            // Check if draft file exists
            if (!$hasilFasilitasi->draft_file || !Storage::disk('public')->exists($hasilFasilitasi->draft_file)) {
                return redirect()->back()->with('error', 'File draft belum tersedia. Silakan koordinator membuat draft terlebih dahulu.');
            }

            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.doc';
            return response()->download(
                $this->documentService->getStoragePath($hasilFasilitasi->draft_file),
                $filename,
                ['Content-Type' => 'application/msword']
            );
        } catch (\Exception $e) {
            Log::error('Error downloading Word hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen PDF hasil fasilitasi
     * Dapat diakses oleh semua tim member dan admin
     * 
     * @param Permohonan $permohonan
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadPdf(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Check access: must be team member, admin, or kepala badan
            if (!$this->isTimMember($permohonan) && !$this->isAdmin() && !$this->isKepalaBadan()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk dokumen ini');
            }

            // Check if draft exists
            if (!$hasilFasilitasi->draft_file) {
                return redirect()->back()->with('error', 'Draft dokumen belum tersedia. Silakan koordinator membuat draft terlebih dahulu.');
            }

            // Get PDF path (same as Word but with .pdf extension)
            $pdfPath = str_replace('.doc', '.pdf', $hasilFasilitasi->draft_file);
            
            // Check if PDF file exists
            if (!Storage::disk('public')->exists($pdfPath)) {
                return redirect()->back()->with('error', 'File PDF belum tersedia. Silakan koordinator membuat ulang draft.');
            }

            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.pdf';
            return response()->download(
                Storage::disk('public')->path($pdfPath),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error downloading PDF hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }

    /**
     * Preview dokumen PDF hasil fasilitasi di browser
     * Dapat diakses oleh semua tim member dan admin
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function previewPdf(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Check access: must be team member, admin, or kepala badan
            if (!$this->isTimMember($permohonan) && !$this->isAdmin() && !$this->isKepalaBadan()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk dokumen ini');
            }

            // Check if draft exists
            if (!$hasilFasilitasi->draft_file) {
                return redirect()->back()->with('error', 'Draft dokumen belum tersedia. Silakan koordinator membuat draft terlebih dahulu.');
            }

            // Get PDF path (same as Word but with .pdf extension)
            $pdfPath = str_replace('.doc', '.pdf', $hasilFasilitasi->draft_file);
            
            // Check if PDF file exists
            if (!Storage::disk('public')->exists($pdfPath)) {
                return redirect()->back()->with('error', 'File PDF belum tersedia. Silakan koordinator membuat ulang draft.');
            }

            // Stream PDF file to browser (inline preview)
            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.pdf';
            return response()->file(
                Storage::disk('public')->path($pdfPath),
                ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="' . $filename . '"']
            );
        } catch (\Exception $e) {
            Log::error('Error previewing PDF hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menampilkan preview PDF: ' . $e->getMessage());
        }
    }

    // ============================================================
    // DRAFT FINAL OPERATIONS
    // ============================================================

    /**
     * Upload draft final PDF (admin/koordinator only)
     * Draft final adalah file yang sudah dilengkapi kop surat secara manual
     * 
     * @param Request $request
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadDraftFinal(Request $request, Permohonan $permohonan)
    {
        try {
            // Only admin or koordinator can upload draft final
            if (!$this->isAdmin() && !$this->isKoordinator($permohonan)) {
                return redirect()->back()->with('error', 'Hanya admin atau koordinator yang dapat mengupload draft final.');
            }

            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Validate file must be PDF
            $request->validate([
                'draft_final_file' => 'required|file|mimes:pdf|max:10240', // max 10MB
            ], [
                'draft_final_file.required' => 'File PDF wajib diupload',
                'draft_final_file.mimes' => 'File harus berformat PDF',
                'draft_final_file.max' => 'Ukuran file maksimal 10MB',
            ]);

            // Delete old draft final if exists
            if ($hasilFasilitasi->draft_final_file && Storage::disk('public')->exists($hasilFasilitasi->draft_final_file)) {
                Storage::disk('public')->delete($hasilFasilitasi->draft_final_file);
            }

            // Store new draft final
            $file = $request->file('draft_final_file');
            $filename = 'Hasil_Fasilitasi_Final_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '_' . time() . '.pdf';
            $filepath = $file->storeAs('hasil-fasilitasi/final', $filename, 'public');

            // Update hasil fasilitasi
            $hasilFasilitasi->update([
                'draft_final_file' => $filepath,
                'updated_by' => Auth::id(),
            ]);

            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('draft_final_uploaded')
                ->withProperties([
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                    'file_name' => $filename,
                ])
                ->log('Draft final PDF hasil fasilitasi berhasil diupload');

            // Notifikasi ke semua anggota tim dan admin
            $this->notifyTeamMembers(
                $permohonan,
                'Draft Final PDF Diupload',
                'Draft final PDF hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' telah diupload dan siap untuk diajukan ke Kepala Badan.',
                'success'
            );

            return redirect()->back()->with('success', 'Draft final PDF berhasil diupload. Dokumen siap untuk diajukan ke Kepala Badan.');
        } catch (\Exception $e) {
            Log::error('Error uploading draft final: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupload draft final: ' . $e->getMessage());
        }
    }

    /**
     * Download draft final PDF yang telah dilengkapi kop surat
     * Dapat diakses oleh semua yang berwenang
     * 
     * @param Permohonan $permohonan
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadDraftFinal(Permohonan $permohonan)
    {
        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Check if draft final exists
            if (!$hasilFasilitasi->draft_final_file || !Storage::disk('public')->exists($hasilFasilitasi->draft_final_file)) {
                return redirect()->back()->with('error', 'Draft final PDF belum tersedia.');
            }

            $filename = 'Hasil_Fasilitasi_Final_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.pdf';
            return response()->download(
                Storage::disk('public')->path($hasilFasilitasi->draft_final_file),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error downloading draft final PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh draft final PDF: ' . $e->getMessage());
        }
    }

    /**
     * Submit hasil fasilitasi ke Kepala Badan untuk persetujuan
     * Hanya dapat dilakukan oleh admin setelah draft final diupload
     * 
     * @param Permohonan $permohonan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitToKaban(Permohonan $permohonan)
    {
        try {
            // Only admin can submit to kaban
            if (!$this->isAdmin()) {
                return redirect()->back()->with('error', 'Hanya admin yang dapat mengajukan dokumen ke Kepala Badan.');
            }

            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            // Check if draft final exists
            if (!$hasilFasilitasi->draft_final_file) {
                return redirect()->back()->with('error', 'Draft final PDF belum diupload. Silakan upload terlebih dahulu.');
            }

            // Check if already submitted or approved
            if (in_array($hasilFasilitasi->status_draft, ['menunggu_persetujuan_kaban', 'disetujui_kaban'])) {
                return redirect()->back()->with('info', 'Dokumen sudah diajukan/disetujui oleh Kepala Badan.');
            }

            // Update status
            $hasilFasilitasi->update([
                'status_draft' => 'menunggu_persetujuan_kaban',
                'tanggal_diajukan_kaban' => now(),
                'updated_by' => Auth::id(),
            ]);

            activity()
                ->performedOn($hasilFasilitasi)
                ->causedBy(Auth::user())
                ->event('submitted_to_kaban')
                ->withProperties([
                    'kabupaten_kota' => $permohonan->kabupatenKota->nama,
                    'status' => 'menunggu_persetujuan_kaban',
                ])
                ->log('Hasil fasilitasi diajukan ke Kepala Badan untuk persetujuan');

            // Notifikasi ke Kepala Badan dan Admin
            $this->notifyAdminAndKaban(
                $permohonan,
                'Dokumen Menunggu Persetujuan',
                'Dokumen hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' telah diajukan dan menunggu persetujuan Kepala Badan.',
                'warning'
            );

            // Notifikasi ke tim bahwa dokumen sudah diajukan
            $this->notifyTeamMembers(
                $permohonan,
                'Dokumen Diajukan ke Kepala Badan',
                'Dokumen hasil fasilitasi untuk ' . $permohonan->kabupatenKota->nama . ' telah diajukan ke Kepala Badan untuk persetujuan.',
                'info'
            );

            return redirect()->back()->with('success', 'Dokumen hasil fasilitasi berhasil diajukan ke Kepala Badan untuk persetujuan.');
        } catch (\Exception $e) {
            Log::error('Error submitting to kaban: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengajukan dokumen: ' . $e->getMessage());
        }
    }
}
