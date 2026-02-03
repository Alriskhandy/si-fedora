<?php

namespace App\Http\Controllers;

use App\Models\JadwalFasilitasi;
use App\Models\MasterJenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JadwalFasilitasiController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalFasilitasi::with(['dibuatOleh', 'jenisDokumen']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tahun_anggaran', 'like', '%' . $request->search . '%')
                    ->orWhere('jenis_dokumen', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_dokumen')) {
            $query->where('jenis_dokumen', $request->jenis_dokumen);
        }

        $jadwalFasilitasi = $query->latest()->paginate(10);

        return view('pages.jadwal-fasilitasi.index', compact('jadwalFasilitasi'));
    }

    public function create()
    {
        $jenisdokumen = MasterJenisDokumen::where('status', true)->get();
        return view('pages.jadwal-fasilitasi.create', compact('jenisdokumen'));
    }

    public function store(Request $request)
    {
        // Get valid jenis dokumen from database
        $validJenisDokumen = MasterJenisDokumen::where('status', true)
            ->pluck('id')
            ->toArray();

        $request->validate([
            'tahun_anggaran' => 'required|integer|min:2000|max:2100',
            'jenis_dokumen' => 'required|in:' . implode(',', $validJenisDokumen),
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'batas_permohonan' => 'nullable|date|before_or_equal:tanggal_mulai',
            'undangan_file' => 'nullable|file|mimes:pdf|max:5120',
            'status' => 'required|in:draft,published,closed',
        ]);

        $data = [
            'tahun_anggaran' => $request->tahun_anggaran,
            'jenis_dokumen' => strtolower($request->jenis_dokumen),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'batas_permohonan' => $request->batas_permohonan,
            'status' => $request->status,
            'created_by' => Auth::user()->id,
        ];

        if ($request->hasFile('undangan_file')) {
            $file = $request->file('undangan_file');
            $filename = Str::random(40) . '.pdf';
            
            // Simpan ke storage/app/public/undangan
            $path = $file->storeAs('undangan', $filename, 'public');
            $data['undangan_file'] = $path;
        }

        JadwalFasilitasi::create($data);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil ditambahkan.');
    }

    public function show(JadwalFasilitasi $jadwal)
    {
        $jadwal->load(['permohonan.kabupatenKota', 'dibuatOleh'])->with(['jenisDokumen']);
        return view('pages.jadwal-fasilitasi.show', compact('jadwal'));
    }

    public function edit(JadwalFasilitasi $jadwal)
    {
        $jenisdokumen = MasterJenisDokumen::where('status', true)->get();
        return view('pages.jadwal-fasilitasi.edit', compact('jadwal', 'jenisdokumen'));
    }

    public function update(Request $request, JadwalFasilitasi $jadwal)
    {
        // Get valid jenis dokumen from database
        $validJenisDokumen = MasterJenisDokumen::where('status', true)
            ->pluck('id')
            ->toArray();

        $request->validate([
            'tahun_anggaran' => 'required|integer|min:2000|max:2100',
            'jenis_dokumen' => 'required|in:' . implode(',', $validJenisDokumen),
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'batas_permohonan' => 'nullable|date|before_or_equal:tanggal_mulai',
            'undangan_file' => 'nullable|file|mimes:pdf|max:5120',
            'status' => 'required|in:draft,published,closed',
        ]);

        $data = [
            'tahun_anggaran' => $request->tahun_anggaran,
            'jenis_dokumen' => strtolower($request->jenis_dokumen),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'batas_permohonan' => $request->batas_permohonan,
            'status' => $request->status,
            'updated_by' => Auth::user()->id,
        ];

        if ($request->hasFile('undangan_file')) {
            // Hapus file lama jika ada
            if ($jadwal->undangan_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($jadwal->undangan_file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($jadwal->undangan_file);
            }
            
            $file = $request->file('undangan_file');
            $filename = \Illuminate\Support\Str::random(40) . '.pdf';
            
            // Simpan ke storage/app/public/undangan
            $path = $file->storeAs('undangan', $filename, 'public');
            $data['undangan_file'] = $path;
        }

        $jadwal->update($data);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil diperbarui.');
    }

    public function destroy(JadwalFasilitasi $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil dihapus.');
    }

    public function download(JadwalFasilitasi $jadwal)
    {
        if (!$jadwal->undangan_file) {
            abort(404, 'File tidak tersedia');
        }

        $filePath = storage_path('app/public/' . $jadwal->undangan_file);

        if (!file_exists($filePath)) {
            // Log untuk debugging
            Log::error('File not found', [
                'jadwal_id' => $jadwal->id,
                'undangan_file' => $jadwal->undangan_file,
                'expected_path' => $filePath,
                'file_exists' => file_exists($filePath)
            ]);
            
            abort(404, 'File tidak ditemukan. Silakan hubungi administrator.');
        }

        $filename = 'Penyampaian_Jadwal_' . str_replace(' ', '_', $jadwal->jenisDokumen->nama) . '_' . $jadwal->tahun_anggaran . '.pdf';
        
        return response()->download($filePath, $filename);
    }
}
