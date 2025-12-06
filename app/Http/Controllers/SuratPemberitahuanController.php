<?php

namespace App\Http\Controllers;

use App\Models\SuratPemberitahuan;
use App\Models\JadwalFasilitasi;
use App\Models\KabupatenKota;
use Illuminate\Http\Request;
use App\Jobs\SendSuratPemberitahuanJob;
use Illuminate\Support\Facades\Storage;

class SuratPemberitahuanController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratPemberitahuan::with(['jadwalFasilitasi', 'kabupatenKota']);

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
                ->orWhere('nomor_surat', 'like', '%' . $request->search . '%')
                ->orWhere('perihal', 'like', '%' . $request->search . '%');
        }

        $suratPemberitahuan = $query->latest()->paginate(10);

        return view('surat-pemberitahuan.index', compact('suratPemberitahuan'));
    }

    public function create()
    {
        // Jadwal fasilitasi sekarang per-permohonan, tidak ada status 'published' lagi
        // Temporarily disabled - perlu konfirmasi apakah surat pemberitahuan masih perlu jadwal_fasilitasi_id
        $jadwalFasilitasi = collect(); // Empty collection untuk sementara
        $kabupatenKota = KabupatenKota::where('is_active', true)->get();

        return view('surat-pemberitahuan.create', compact('jadwalFasilitasi', 'kabupatenKota'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id|unique:surat_pemberitahuan,kabupaten_kota_id,NULL,id,jadwal_fasilitasi_id,' . $request->jadwal_fasilitasi_id,
            'nomor_surat' => 'nullable|string|unique:surat_pemberitahuan,nomor_surat',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:200',
            'isi_surat' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        $file_path = null;
        if ($request->hasFile('file_path')) {
            $file_path = $request->file('file_path')->store('surat-pemberitahuan', 'public');
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
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('surat-pemberitahuan.index')->with('success', 'Surat pemberitahuan berhasil ditambahkan.');
    }

    public function show(SuratPemberitahuan $suratPemberitahuan)
    {
        return view('surat-pemberitahuan.show', compact('suratPemberitahuan'));
    }

    public function edit(SuratPemberitahuan $suratPemberitahuan)
    {
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')->get();
        $kabupatenKota = KabupatenKota::where('is_active', true)->get();

        return view('surat-pemberitahuan.edit', compact('suratPemberitahuan', 'jadwalFasilitasi', 'kabupatenKota'));
    }

    public function update(Request $request, SuratPemberitahuan $suratPemberitahuan)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id|unique:surat_pemberitahuan,kabupaten_kota_id,' . $suratPemberitahuan->id . ',id,jadwal_fasilitasi_id,' . $request->jadwal_fasilitasi_id,
            'nomor_surat' => 'nullable|string|unique:surat_pemberitahuan,nomor_surat,' . $suratPemberitahuan->id,
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:200',
            'isi_surat' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $file_path = $suratPemberitahuan->file_path;
        if ($request->hasFile('file_path')) {
            // Hapus file lama kalo ada
            if ($suratPemberitahuan->file_path) {
                \Storage::disk('public')->delete($suratPemberitahuan->file_path);
            }
            $file_path = $request->file('file_path')->store('surat-pemberitahuan', 'public');
        }

        $suratPemberitahuan->update([
            'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
            'kabupaten_kota_id' => $request->kabupaten_kota_id,
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'perihal' => $request->perihal,
            'isi_surat' => $request->isi_surat,
            'file_path' => $file_path,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('surat-pemberitahuan.index')->with('success', 'Surat pemberitahuan berhasil diperbarui.');
    }

    public function destroy(SuratPemberitahuan $suratPemberitahuan)
    {
        // Hapus file kalo ada
        if ($suratPemberitahuan->file_path) {
            Storage::disk('public')->delete($suratPemberitahuan->file_path);
        }

        $suratPemberitahuan->delete();
        return redirect()->route('surat-pemberitahuan.index')->with('success', 'Surat pemberitahuan berhasil dihapus.');
    }

    public function send(SuratPemberitahuan $suratPemberitahuan)
    {
        // Update status surat
        $suratPemberitahuan->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);

        // // Dispatch job untuk kirim notifikasi WhatsApp
        // SendSuratPemberitahuanJob::dispatch($suratPemberitahuan);

        // // Get user count for feedback
        // $userCount = $suratPemberitahuan->kabupatenKota
        //     ->users()
        //     ->whereNotNull('phone')
        //     ->count();

        // if ($userCount > 0) {
        //     return redirect()->back()->with('success', "Surat berhasil dikirim. Notifikasi WhatsApp akan dikirim ke {$userCount} user.");
        // }

        return redirect()->back()->with('success', 'Surat berhasil dikirim. Tidak ada user dengan nomor telepon yang terdaftar.');
    }

    public function download(SuratPemberitahuan $suratPemberitahuan)
    {
        if ($suratPemberitahuan->file_path) {
            return response()->download(storage_path('app/public/' . $suratPemberitahuan->file_path));
        }

        return redirect()->back()->with('error', 'File surat tidak ditemukan.');
    }
}
