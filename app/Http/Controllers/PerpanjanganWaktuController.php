<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $this->authorize('viewAny', PerpanjanganWaktu::class);

        $perpanjanganList = PerpanjanganWaktu::with(['permohonan.kabupatenKota', 'permohonan.jenisDokumen', 'user', 'admin'])
            ->when(auth()->user()->hasRole('pemohon'), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate(15);

        return view('pages.perpanjangan-waktu.index', compact('perpanjanganList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $permohonanId = $request->query('permohonan_id');

        if (!$permohonanId) {
            return redirect()->back()->with('error', 'Permohonan ID tidak ditemukan');
        }

        $permohonan = Permohonan::with('jadwalFasilitasi')->findOrFail($permohonanId);

        // Cek authorization
        $this->authorize('create', [PerpanjanganWaktu::class, $permohonan]);

        return view('pages.perpanjangan-waktu.create', compact('permohonan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $permohonan = Permohonan::findOrFail($request->permohonan_id);

        $this->authorize('create', [PerpanjanganWaktu::class, $permohonan]);

        $validated = $request->validate([
            'permohonan_id' => 'required|exists:permohonan,id',
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
        $filePath = $request->file('surat_permohonan')->store(
            'perpanjangan_waktu/' . $permohonan->id,
            'public'
        );

        // Create perpanjangan
        $perpanjangan = PerpanjanganWaktu::create([
            'permohonan_id' => $validated['permohonan_id'],
            'user_id' => auth()->id(),
            'alasan' => $validated['alasan'],
            'surat_permohonan' => $filePath,
        ]);

        // TODO: Send notification to admin
        // event(new PerpanjanganWaktuCreated($perpanjangan));

        return redirect()->route('permohonan.show', $permohonan)
            ->with('success', 'Permohonan perpanjangan waktu berhasil diajukan. Mohon menunggu persetujuan admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PerpanjanganWaktu $perpanjanganWaktu)
    {
        $this->authorize('view', $perpanjanganWaktu);

        $perpanjanganWaktu->load(['permohonan.kabupatenKota', 'user', 'admin']);

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
        $filePath = $request->file('file_surat')->store(
            'perpanjangan_waktu/' . $perpanjanganWaktu->permohonan_id,
            'public'
        );

        $perpanjanganWaktu->update([
            'surat_permohonan' => $filePath,
        ]);

        return redirect()->route('permohonan.show', $perpanjanganWaktu->permohonan_id)
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

        // Update perpanjangan waktu
        $perpanjanganWaktu->update([
            'catatan_admin' => $validated['catatan_admin'],
            'diproses_oleh' => auth()->id(),
            'diproses_at' => now(),
        ]);

        // Update jadwal fasilitasi
        if ($perpanjanganWaktu->permohonan->jadwalFasilitasi) {
            $perpanjanganWaktu->permohonan->jadwalFasilitasi->update([
                'batas_permohonan' => $validated['batas_permohonan_baru'],
            ]);
        }

        return redirect()->route('perpanjangan-waktu.index')
            ->with('success', 'Perpanjangan waktu berhasil diproses. Batas waktu upload telah diperbarui.');
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

        return redirect()->route('permohonan.show', $permohonanId)
            ->with('success', 'Permohonan perpanjangan waktu berhasil dihapus.');
    }
}
