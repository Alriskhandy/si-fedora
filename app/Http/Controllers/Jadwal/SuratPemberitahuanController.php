<?php

namespace App\Http\Controllers\Jadwal;

use App\Http\Controllers\Controller;
use App\Models\JadwalFasilitasi;
use App\Models\KabupatenKota;
use App\Models\SuratPemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Surat pemberitahuan jadwal fasilitasi kepada kabupaten/kota.
 *
 * Akses: surat-pemberitahuan.manage.
 * View: resources/views/pages/jadwal/surat-pemberitahuan
 */
class SuratPemberitahuanController extends Controller
{
    /**
     * Daftar surat pemberitahuan.
     */
    public function index(Request $request)
    {
        $query = SuratPemberitahuan::with(['jadwalFasilitasi', 'kabupatenKota']);

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%');
            })
                ->orWhere('nomor_surat', 'like', '%'.$request->search.'%')
                ->orWhere('perihal', 'like', '%'.$request->search.'%');
        }

        $suratPemberitahuan = $query->latest()->paginate(10);

        return view('pages.jadwal.surat-pemberitahuan.index', compact('suratPemberitahuan'));
    }

    /**
     * Form buat surat pemberitahuan.
     */
    public function create()
    {
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')->get();
        $kabupatenKota = KabupatenKota::where('is_active', true)->get();

        return view('pages.jadwal.surat-pemberitahuan.create', compact('jadwalFasilitasi', 'kabupatenKota'));
    }

    /**
     * Simpan surat pemberitahuan beserta file (disk dokumen).
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id|unique:surat_pemberitahuan,kabupaten_kota_id,NULL,id,jadwal_fasilitasi_id,'.$request->jadwal_fasilitasi_id,
            'nomor_surat' => 'nullable|string|unique:surat_pemberitahuan,nomor_surat',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:200',
            'isi_surat' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        $file_path = null;
        if ($request->hasFile('file_path')) {
            $file_path = $request->file('file_path')->store('surat-pemberitahuan', 'dokumen');
        }

        SuratPemberitahuan::create([
            'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
            'kabupaten_kota_id' => $request->kabupaten_kota_id,
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'perihal' => $request->perihal,
            'isi_surat' => $request->isi_surat,
            'file_path' => $file_path,
            'status' => 'draft',
            'created_by' => Auth::user()->id,
        ]);

        return redirect()->route('surat-pemberitahuan.index')->with('success', 'Surat pemberitahuan berhasil ditambahkan.');
    }

    /**
     * Detail surat pemberitahuan.
     */
    public function show(SuratPemberitahuan $suratPemberitahuan)
    {
        return view('pages.jadwal.surat-pemberitahuan.show', compact('suratPemberitahuan'));
    }

    /**
     * Form ubah surat pemberitahuan.
     */
    public function edit(SuratPemberitahuan $suratPemberitahuan)
    {
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')->get();
        $kabupatenKota = KabupatenKota::where('is_active', true)->get();

        return view('pages.jadwal.surat-pemberitahuan.edit', compact('suratPemberitahuan', 'jadwalFasilitasi', 'kabupatenKota'));
    }

    /**
     * Simpan perubahan surat; file lama dihapus bila diganti.
     */
    public function update(Request $request, SuratPemberitahuan $suratPemberitahuan)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id|unique:surat_pemberitahuan,kabupaten_kota_id,'.$suratPemberitahuan->id.',id,jadwal_fasilitasi_id,'.$request->jadwal_fasilitasi_id,
            'nomor_surat' => 'nullable|string|unique:surat_pemberitahuan,nomor_surat,'.$suratPemberitahuan->id,
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:200',
            'isi_surat' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $file_path = $suratPemberitahuan->file_path;
        if ($request->hasFile('file_path')) {
            // Hapus file lama kalo ada
            if ($suratPemberitahuan->file_path) {
                Storage::disk('dokumen')->delete($suratPemberitahuan->file_path);
            }
            $file_path = $request->file('file_path')->store('surat-pemberitahuan', 'dokumen');
        }

        $suratPemberitahuan->update([
            'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
            'kabupaten_kota_id' => $request->kabupaten_kota_id,
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'perihal' => $request->perihal,
            'isi_surat' => $request->isi_surat,
            'file_path' => $file_path,
            'updated_by' => Auth::user()->id,
        ]);

        return redirect()->route('surat-pemberitahuan.index')->with('success', 'Surat pemberitahuan berhasil diperbarui.');
    }

    /**
     * Hapus surat beserta filenya.
     */
    public function destroy(SuratPemberitahuan $suratPemberitahuan)
    {
        // Hapus file kalo ada
        if ($suratPemberitahuan->file_path) {
            Storage::disk('dokumen')->delete($suratPemberitahuan->file_path);
        }

        $suratPemberitahuan->delete();

        return redirect()->route('surat-pemberitahuan.index')->with('success', 'Surat pemberitahuan berhasil dihapus.');
    }

    /**
     * Tandai surat sebagai terkirim. Notifikasi WhatsApp untuk surat ini belum diimplementasikan.
     */
    public function send(SuratPemberitahuan $suratPemberitahuan)
    {
        // Update status surat
        $suratPemberitahuan->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Surat berhasil dikirim.');
    }

    /**
     * Unduh file surat pemberitahuan.
     */
    public function download(SuratPemberitahuan $suratPemberitahuan)
    {
        if ($suratPemberitahuan->file_path) {
            return response()->download(storage_path('app/private/'.$suratPemberitahuan->file_path));
        }

        return redirect()->back()->with('error', 'File surat tidak ditemukan.');
    }
}
