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
use App\Services\HasilFasilitasiDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class HasilFasilitasiController extends Controller
{
    protected $documentService;

    public function __construct(HasilFasilitasiDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Check if user is koordinator for this permohonan
     */
    private function isKoordinator(Permohonan $permohonan)
    {
        // Get kabupaten_kota_id - handle both field names
        $kabkotaId = $permohonan->kabupaten_kota_id ?? $permohonan->kab_kota_id;
        
        // Get jenis_dokumen_id directly from permohonan
        $jenisDokumenId = $permohonan->jenis_dokumen_id;
        
        // Get assignment for debugging
        $assignment = UserKabkotaAssignment::where('user_id', Auth::id())
            ->where('kabupaten_kota_id', $kabkotaId)
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
            'kabupaten_kota_id' => $permohonan->kabupaten_kota_id,
            'kab_kota_id' => $permohonan->kab_kota_id,
            'used_kabkota_id' => $kabkotaId,
            'jenis_dokumen_id' => $jenisDokumenId,
            'tahun' => $permohonan->tahun,
            'assignment_found' => $assignment ? 'YES' : 'NO',
            'result' => $isKoord
        ]);
        
        return $isKoord;
    }

    /**
     * Check if user is member of this permohonan's tim (fasilitator or verifikator)
     */
    private function isTimMember(Permohonan $permohonan)
    {
        // Get kabupaten_kota_id - handle both field names
        $kabkotaId = $permohonan->kabupaten_kota_id ?? $permohonan->kab_kota_id;
        
        // Get jenis_dokumen_id directly from permohonan
        $jenisDokumenId = $permohonan->jenis_dokumen_id;
        
        // Get assignment for debugging
        $assignment = UserKabkotaAssignment::where('user_id', Auth::id())
            ->where('kabupaten_kota_id', $kabkotaId)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('tahun', $permohonan->tahun)
            ->whereIn('role_type', ['fasilitator', 'verifikator'])
            ->where('is_active', true)
            ->first();
        
        $isMember = $assignment !== null;
        
        Log::info('isTimMember Check', [
            'user_id' => Auth::id(),
            'permohonan_id' => $permohonan->id,
            'kabupaten_kota_id' => $permohonan->kabupaten_kota_id,
            'kab_kota_id' => $permohonan->kab_kota_id,
            'used_kabkota_id' => $kabkotaId,
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
     */
    private function canManageItem($item, Permohonan $permohonan)
    {
        return $item->user_id == Auth::id() || $this->isKoordinator($permohonan);
    }

    /**
     * Tampilkan daftar permohonan untuk input hasil fasilitasi (Fasilitator)
     */
    public function index(Request $request)
    {
        // Get user's tim assignments
        $userAssignments = UserKabkotaAssignment::where('user_id', Auth::id())
            ->where('role_type', 'fasilitator')
            ->where('is_active', true)
            ->get();

        $query = Permohonan::with(['kabupatenKota', 'undanganPelaksanaan', 'hasilFasilitasi'])
            ->where(function($q) use ($userAssignments) {
                foreach ($userAssignments as $assignment) {
                    // Get jenis_dokumen name from id
                    $jenisDokumen = MasterJenisDokumen::find($assignment->jenis_dokumen_id);
                    
                    $q->orWhere(function($subQ) use ($assignment, $jenisDokumen) {
                        // Check both field names for kabupaten_kota_id
                        $subQ->where(function($kabQ) use ($assignment) {
                            $kabQ->where('kabupaten_kota_id', $assignment->kabupaten_kota_id)
                                ->orWhere('kab_kota_id', $assignment->kabupaten_kota_id);
                        });
                        $subQ->where('tahun', $assignment->tahun);
                        
                        if ($jenisDokumen) {
                            $subQ->where('jenis_dokumen', $jenisDokumen->nama);
                        }
                    });
                }
            });

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

        return view('hasil-fasilitasi.index', compact('permohonan'));
    }

    /**
     * Tampilkan form untuk membuat hasil fasilitasi
     */
    public function create(Permohonan $permohonan)
    {
        // Check if user is member of this tim
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
        // Permohonan punya jenis_dokumen enum ('rkpd', 'rpd', 'rpjmd')
        // Master bab punya jenis_dokumen_id (foreign key ke tabel jenis_dokumen)
        $masterBabList = collect();
        if ($permohonan->jenis_dokumen) {
            // Cari jenis_dokumen_id berdasarkan nama jenis_dokumen
            $jenisDokumen = \App\Models\MasterJenisDokumen::where('nama', strtoupper($permohonan->jenis_dokumen))->first();

            if ($jenisDokumen) {
                $masterBabList = MasterBab::where('jenis_dokumen_id', $jenisDokumen->id)
                    ->whereNull('parent_id')
                    ->orderBy('urutan')
                    ->get();
            }
        }

        // Load hasil fasilitasi dengan relasi
        $hasilFasilitasi = $permohonan->hasilFasilitasi;
        $hasilFasilitasi->load('hasilSistematika.masterBab', 'hasilSistematika.user', 'hasilUrusan.masterUrusan', 'hasilUrusan.user');

        // Check if current user is koordinator (fasilitator dengan is_pic=true)
        $isKoordinator = $this->isKoordinator($permohonan);

        // Get tim info untuk ditampilkan
        $kabkotaId = $permohonan->kabupaten_kota_id ?? $permohonan->kab_kota_id;
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

        return view('hasil-fasilitasi.create', compact('permohonan', 'masterUrusanList', 'masterBabList', 'hasilFasilitasi', 'isKoordinator', 'timInfo'));
    }

    /**
     * Simpan hasil fasilitasi
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
     * Tampilkan detail hasil fasilitasi
     */
    public function show(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi) {
            return redirect()->route('hasil-fasilitasi.create', $permohonan)
                ->with('info', 'Hasil fasilitasi belum dibuat.');
        }

        $hasilFasilitasi->load('hasilUrusan.masterUrusan', 'hasilSistematika');

        return view('hasil-fasilitasi.show', compact('permohonan', 'hasilFasilitasi'));
    }

    /**
     * Ajukan hasil fasilitasi ke admin_peran
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

    /**
     * Download file draft
     */
    public function download(Permohonan $permohonan)
    {
        $hasilFasilitasi = $permohonan->hasilFasilitasi;

        if (!$hasilFasilitasi || !$hasilFasilitasi->draft_file) {
            return back()->with('error', 'File draft tidak ditemukan.');
        }

        $filepath = storage_path('app/public/' . $hasilFasilitasi->draft_file);

        if (!file_exists($filepath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        return response()->download($filepath);
    }

    /**
     * Simpan item sistematika
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

            $sistematika->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item sistematika berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Simpan item urusan
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

            $urusan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item urusan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate dokumen hasil fasilitasi dalam format Word
     */
    public function generate(Permohonan $permohonan)
    {
        // Only koordinator can generate documents
        if (!$this->isKoordinator($permohonan)) {
            return redirect()->back()->with('error', 'Hanya koordinator yang dapat generate dokumen');
        }

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
            }

            $sistematika = $hasilFasilitasi->hasilSistematika()
                ->with('masterBab')
                ->orderBy('master_bab_id')
                ->orderBy('id')
                ->get();

            $urusan = $hasilFasilitasi->hasilUrusan()
                ->with('masterUrusan')
                ->orderBy('id')
                ->get();

            // Generate document using service
            $content = $this->documentService->generateWordDocument($permohonan, $sistematika, $urusan);

            // Save to file
            $filename = 'Hasil_Fasilitasi_' . $permohonan->kabupatenKota->nama . '_' . date('Y') . '.doc';
            $filepath = $this->documentService->saveDocument($content, $filename);

            // Update draft_file in hasil_fasilitasi
            $hasilFasilitasi->update([
                'draft_file' => $filepath,
                'updated_by' => Auth::id()
            ]);

            return response()->download(
                $this->documentService->getStoragePath($filepath),
                $filename,
                ['Content-Type' => 'application/msword']
            );
        } catch (\Exception $e) {
            Log::error('Error generating hasil fasilitasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Generate dokumen hasil fasilitasi dalam format PDF
     */
    public function generatePdf(Permohonan $permohonan)
    {
        // Only koordinator can generate documents
        if (!$this->isKoordinator($permohonan)) {
            return redirect()->back()->with('error', 'Hanya koordinator yang dapat generate dokumen');
        }

        try {
            $hasilFasilitasi = $permohonan->hasilFasilitasi;

            if (!$hasilFasilitasi) {
                return redirect()->back()->with('error', 'Hasil fasilitasi belum tersedia');
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
