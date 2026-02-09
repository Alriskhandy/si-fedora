<?php

namespace App\Http\Controllers;

use App\Models\JadwalFasilitasi;
use App\Models\MasterJenisDokumen;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JadwalFasilitasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = JadwalFasilitasi::with(['dibuatOleh', 'jenisDokumen']);

        // Untuk non-admin, hanya tampilkan jadwal yang sudah published
        if (!$user->hasRole('admin_peran')) {
            $query->where('status', 'published');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_dokumen')) {
            $query->where('jenis_dokumen', $request->jenis_dokumen);
        }

        if ($request->filled('tahun_anggaran')) {
            $query->where('tahun_anggaran', $request->tahun_anggaran);
        }

        $jadwalFasilitasi = $query->latest()->paginate(10);

        $filterOptions = [
            'tahunList' => JadwalFasilitasi::where('status', 'published')
                ->distinct()
                ->orderBy('tahun_anggaran', 'desc')
                ->pluck('tahun_anggaran'),
            'jenisDokumenList' => MasterJenisDokumen::all()
        ];

        return view('pages.jadwal-fasilitasi.index', compact('jadwalFasilitasi', 'filterOptions'));
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
            'dibuat_oleh' => Auth::user()->id,
        ];

        if ($request->hasFile('undangan_file')) {
            $file = $request->file('undangan_file');
            $filename = Str::random(40) . '.pdf';
            
            // Simpan ke storage/app/public/undangan
            $path = $file->storeAs('undangan', $filename, 'public');
            $data['undangan_file'] = $path;
        }

        $jadwal = JadwalFasilitasi::create($data);

        // Activity Log
        activity()
            ->performedOn($jadwal)
            ->causedBy(Auth::user())
            ->withProperties([
                'tahun_anggaran' => $jadwal->tahun_anggaran,
                'jenis_dokumen' => $jadwal->jenisDokumen->nama,
                'tanggal_mulai' => $jadwal->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $jadwal->tanggal_selesai->format('Y-m-d'),
                'status' => $jadwal->status,
            ])
            ->log('Membuat jadwal fasilitasi baru');

        // Kirim notifikasi ke semua user (kecuali superadmin)
        $users = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', '!=', 'superadmin')
            ->where('name', '!=', 'auditor')
            ->where('name', '!=', 'admin_peran');
        })->get();

        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'title' => 'Jadwal Fasilitasi Baru',
                'message' => 'Jadwal fasilitasi ' . $jadwal->jenisDokumen->nama . ' tahun ' . $jadwal->tahun_anggaran . ' telah ditambahkan.',
                'type' => 'info',
                'model_type' => get_class($jadwal),
                'model_id' => $jadwal->id,
                'action_url' => route('jadwal.show', $jadwal->id),
            ]);
        }

        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil ditambahkan.');
    }

    public function show(JadwalFasilitasi $jadwal)
    {
        $user = Auth::user();
        
        // Load relationships based on role
        if ($user->hasAnyRole(['auditor', 'kaban', 'admin_peran', 'superadmin'])) {
            // Auditor, Kaban, Admin can see creator info
            $jadwal->load(['permohonan.kabupatenKota', 'dibuatOleh', 'jenisDokumen']);
        } else {
            // Other roles can't see creator info
            $jadwal->load(['permohonan.kabupatenKota', 'jenisDokumen']);
        }
        
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

        // Simpan data lama untuk log
        $oldData = [
            'tahun_anggaran' => $jadwal->tahun_anggaran,
            'jenis_dokumen' => $jadwal->jenisDokumen->nama,
            'status' => $jadwal->status,
        ];

        $jadwal->update($data);
        $jadwal->refresh();

        // Activity Log
        activity()
            ->performedOn($jadwal)
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldData,
                'new' => [
                    'tahun_anggaran' => $jadwal->tahun_anggaran,
                    'jenis_dokumen' => $jadwal->jenisDokumen->nama,
                    'tanggal_mulai' => $jadwal->tanggal_mulai->format('Y-m-d'),
                    'tanggal_selesai' => $jadwal->tanggal_selesai->format('Y-m-d'),
                    'status' => $jadwal->status,
                ]
            ])
            ->log('Mengupdate jadwal fasilitasi');

        // Kirim notifikasi ke semua user (kecuali superadmin)
        $users = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', '!=', 'superadmin')
            ->where('name', '!=', 'auditor')
            ->where('name', '!=', 'admin_peran');
        })->get();

        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'title' => 'Jadwal Fasilitasi Diperbarui',
                'message' => 'Jadwal fasilitasi ' . $jadwal->jenisDokumen->nama . ' tahun ' . $jadwal->tahun_anggaran . ' telah diperbarui.',
                'type' => 'warning',
                'model_type' => get_class($jadwal),
                'model_id' => $jadwal->id,
                'action_url' => route('jadwal.show', $jadwal->id),
            ]);
        }

        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil diperbarui.');
    }

    public function destroy(JadwalFasilitasi $jadwal)
    {
        // Activity Log sebelum delete
        activity()
            ->performedOn($jadwal)
            ->causedBy(Auth::user())
            ->withProperties([
                'tahun_anggaran' => $jadwal->tahun_anggaran,
                'jenis_dokumen' => $jadwal->jenisDokumen->nama,
                'status' => $jadwal->status,
            ])
            ->log('Menghapus jadwal fasilitasi');

        $jadwal->delete();
        
        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil dihapus.');
    }

    public function download(JadwalFasilitasi $jadwal)
    {
        if (!$jadwal->undangan_file) {
            abort(404, 'File tidak tersedia');
        }

        $filePath = storage_path('app/public/' . $jadwal->undangan_file);

        // Activity Log untuk download
        activity()
            ->performedOn($jadwal)
            ->causedBy(Auth::user())
            ->withProperties([
                'tahun_anggaran' => $jadwal->tahun_anggaran,
                'jenis_dokumen' => $jadwal->jenisDokumen->nama,
                'file' => $jadwal->undangan_file,
            ])
            ->log('Mengunduh file jadwal fasilitasi');

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
