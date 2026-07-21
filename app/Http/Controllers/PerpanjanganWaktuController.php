<?php

namespace App\Http\Controllers;

use App\Models\JadwalFasilitasi;
use App\Models\PerpanjanganWaktu;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class PerpanjanganWaktuController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PerpanjanganWaktu::class);

        $perpanjanganList = PerpanjanganWaktu::with(['permohonan.kabupatenKota', 'permohonan.jenisDokumen', 'user', 'admin'])
            ->when(auth()->user()->hasRole('pemohon'), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'menunggu') {
                    $query->whereNull('diproses_at');
                } elseif ($request->status === 'diproses') {
                    $query->whereNotNull('diproses_at');
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('permohonan.kabupatenKota', function ($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15);

        $permohonanLewatBatas = collect();
        $jadwalLewatBatas = collect();

        if (auth()->user()->hasRole('pemohon')) {
            $permohonanLewatBatas = Permohonan::with(['kabupatenKota', 'jenisDokumen', 'jadwalFasilitasi'])
                ->where('user_id', auth()->id())
                ->where('status_akhir', 'belum')
                // Hanya tampilkan yang belum pernah diajukan perpanjangan (hindari request ganda)
                ->whereDoesntHave('perpanjanganWaktu')
                ->get()
                ->filter(function ($p) {
                    return $p->isUploadDeadlinePassed();
                });

            $jadwalLewatBatas = JadwalFasilitasi::availableForExtensionRequest(auth()->id())
                ->with('jenisDokumen')
                ->orderBy('batas_permohonan')
                ->get();
        }

        return view('pages.perpanjangan-waktu.index', compact('perpanjanganList', 'permohonanLewatBatas', 'jadwalLewatBatas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $permohonan = null;
        $jadwal = null;

        if ($request->filled('permohonan_id')) {
            $permohonan = Permohonan::with('jadwalFasilitasi')->findOrFail($request->query('permohonan_id'));
            $this->authorize('create', [PerpanjanganWaktu::class, $permohonan]);
        } elseif ($request->filled('jadwal_fasilitasi_id')) {
            $jadwal = JadwalFasilitasi::with('jenisDokumen')->findOrFail($request->query('jadwal_fasilitasi_id'));
            $this->authorize('create', [PerpanjanganWaktu::class, $jadwal]);
        } else {
            return redirect()->back()->with('error', 'Permohonan atau Jadwal Fasilitasi tidak ditemukan');
        }

        return view('pages.perpanjangan-waktu.create', compact('permohonan', 'jadwal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $permohonan = null;
        $jadwal = null;

        if ($request->filled('permohonan_id')) {
            $permohonan = Permohonan::findOrFail($request->permohonan_id);
            $this->authorize('create', [PerpanjanganWaktu::class, $permohonan]);
        } elseif ($request->filled('jadwal_fasilitasi_id')) {
            $jadwal = JadwalFasilitasi::findOrFail($request->jadwal_fasilitasi_id);
            $this->authorize('create', [PerpanjanganWaktu::class, $jadwal]);
        } else {
            abort(422, 'Permohonan atau Jadwal Fasilitasi wajib diisi.');
        }

        $validated = $request->validate([
            'permohonan_id' => 'nullable|required_without:jadwal_fasilitasi_id|exists:permohonan,id',
            'jadwal_fasilitasi_id' => 'nullable|required_without:permohonan_id|exists:jadwal_fasilitasi,id',
            'alasan' => 'required|string|min:20',
            'surat_permohonan' => 'required|file|mimes:pdf|max:2048',
        ], [
            'alasan.required' => 'Alasan perpanjangan harus diisi',
            'alasan.min' => 'Alasan minimal 20 karakter',
            'surat_permohonan.required' => 'Surat permohonan harus diupload',
            'surat_permohonan.mimes' => 'Surat permohonan harus berformat PDF',
            'surat_permohonan.max' => 'Ukuran file maksimal 2MB',
        ]);

        // Upload file
        $folder = $permohonan
            ? 'perpanjangan_waktu/permohonan_' . $permohonan->id
            : 'perpanjangan_waktu/jadwal_' . $jadwal->id;
        $filePath = $request->file('surat_permohonan')->store($folder, 'public');

        // Create perpanjangan
        $perpanjangan = PerpanjanganWaktu::create([
            'permohonan_id' => $permohonan?->id,
            'jadwal_fasilitasi_id' => $jadwal?->id,
            'user_id' => auth()->id(),
            'alasan' => $validated['alasan'],
            'surat_permohonan' => $filePath,
        ]);

        // TODO: Send notification to admin
        // event(new PerpanjanganWaktuCreated($perpanjangan));

        return $permohonan
            ? redirect()->route('permohonan.show', $permohonan)
                ->with('success', 'Permohonan perpanjangan waktu berhasil diajukan. Mohon menunggu persetujuan admin.')
            : redirect()->route('perpanjangan-waktu.index')
                ->with('success', 'Permohonan perpanjangan waktu untuk jadwal fasilitasi berhasil diajukan. Mohon menunggu persetujuan admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PerpanjanganWaktu $perpanjanganWaktu)
    {
        $this->authorize('view', $perpanjanganWaktu);

        $perpanjanganWaktu->load(['permohonan.kabupatenKota', 'jadwalFasilitasi.jenisDokumen', 'user', 'admin']);

        return view('pages.perpanjangan-waktu.show', compact('perpanjanganWaktu'));
    }

    /**
     * Upload surat permohonan for existing perpanjangan.
     */
    public function uploadSurat(Request $request, PerpanjanganWaktu $perpanjanganWaktu)
    {
        $this->authorize('update', $perpanjanganWaktu);

        $validated = $request->validate([
            'file_surat' => 'required|file|mimes:pdf|max:2048',
        ], [
            'file_surat.required' => 'File surat harus diupload',
            'file_surat.mimes' => 'File harus berformat PDF',
            'file_surat.max' => 'Ukuran file maksimal 2MB',
        ]);

        // Delete old file if exists
        if ($perpanjanganWaktu->surat_permohonan) {
            Storage::disk('public')->delete($perpanjanganWaktu->surat_permohonan);
        }

        // Upload new file
        $folder = $perpanjanganWaktu->permohonan_id
            ? 'perpanjangan_waktu/permohonan_' . $perpanjanganWaktu->permohonan_id
            : 'perpanjangan_waktu/jadwal_' . $perpanjanganWaktu->jadwal_fasilitasi_id;
        $filePath = $request->file('file_surat')->store($folder, 'public');

        $perpanjanganWaktu->update([
            'surat_permohonan' => $filePath,
        ]);

        return $perpanjanganWaktu->permohonan_id
            ? redirect()->route('permohonan.show', $perpanjanganWaktu->permohonan_id)
                ->with('success', 'Surat permohonan berhasil diupload.')
            : redirect()->route('perpanjangan-waktu.index')
                ->with('success', 'Surat permohonan berhasil diupload.');
    }

    /**
     * Process perpanjangan waktu - update jadwal fasilitasi deadline and add admin notes.
     */
    public function process(Request $request, PerpanjanganWaktu $perpanjanganWaktu)
    {
        // Only admin_peran and superadmin can process
        if (!auth()->user()->hasAnyRole(['admin_peran', 'superadmin'])) {
            abort(403, 'Anda tidak memiliki akses untuk memproses perpanjangan waktu.');
        }

        $validated = $request->validate([
            'batas_permohonan_baru' => 'required|date|after:now',
            'catatan_admin' => 'required|string|min:10',
        ], [
            'batas_permohonan_baru.required' => 'Batas waktu baru harus diisi',
            'batas_permohonan_baru.date' => 'Format tanggal tidak valid',
            'batas_permohonan_baru.after' => 'Batas waktu harus lebih dari waktu sekarang',
            'catatan_admin.required' => 'Catatan admin harus diisi',
            'catatan_admin.min' => 'Catatan minimal 10 karakter',
        ]);

        // Update perpanjangan waktu (batas_waktu disimpan per-permohonan, jadwal tidak diubah)
        $perpanjanganWaktu->update([
            'batas_waktu' => $validated['batas_permohonan_baru'],
            'catatan_admin' => $validated['catatan_admin'],
            'diproses_oleh' => auth()->id(),
            'diproses_at' => now(),
        ]);

        $message = $perpanjanganWaktu->permohonan_id
            ? 'Perpanjangan waktu berhasil diproses. Batas waktu upload telah diperbarui.'
            : 'Perpanjangan waktu berhasil diproses. Pemohon kini dapat membuat permohonan untuk jadwal ini.';

        return redirect()->route('perpanjangan-waktu.index')
            ->with('success', $message);
    }

    /**
     * Download surat permohonan.
     */
    public function download(PerpanjanganWaktu $perpanjanganWaktu)
    {
        $this->authorize('view', $perpanjanganWaktu);

        if (!$perpanjanganWaktu->surat_permohonan || !Storage::disk('public')->exists($perpanjanganWaktu->surat_permohonan)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($perpanjanganWaktu->surat_permohonan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerpanjanganWaktu $perpanjanganWaktu)
    {
        $this->authorize('delete', $perpanjanganWaktu);

        // Delete file
        if ($perpanjanganWaktu->surat_permohonan) {
            Storage::disk('public')->delete($perpanjanganWaktu->surat_permohonan);
        }

        $permohonanId = $perpanjanganWaktu->permohonan_id;
        $perpanjanganWaktu->delete();

        return $permohonanId
            ? redirect()->route('permohonan.show', $permohonanId)
                ->with('success', 'Permohonan perpanjangan waktu berhasil dihapus.')
            : redirect()->route('perpanjangan-waktu.index')
                ->with('success', 'Permohonan perpanjangan waktu berhasil dihapus.');
    }
}
