<?php

namespace App\Http\Controllers;

use App\Models\JadwalFasilitasi;
use App\Models\TahunAnggaran;
use App\Models\JenisDokumen;
use Illuminate\Http\Request;

class JadwalFasilitasiController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalFasilitasi::with(['tahunAnggaran', 'jenisDokumen']);

        if ($request->filled('search')) {
            $query->whereHas('jenisDokumen', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('tahunAnggaran', function($q) use ($request) {
                $q->where('tahun', 'like', '%' . $request->search . '%');
            });
        }

        $jadwalFasilitasi = $query->latest()->paginate(10);

        return view('jadwal-fasilitasi.index', compact('jadwalFasilitasi'));
    }

    public function create()
    {
        $tahunAnggaran = TahunAnggaran::where('is_active', true)->get();
        $jenisDokumen = JenisDokumen::where('is_active', true)->get();
        
        return view('jadwal-fasilitasi.create', compact('tahunAnggaran', 'jenisDokumen'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
    //         'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
    //         'nama_kegiatan' => 'required|string|max:200',
    //         'tanggal_mulai' => 'required|date',
    //         'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
    //         'batas_permohonan' => 'required|date|after_or_equal:tanggal_mulai',
    //         'keterangan' => 'nullable|string',
    //         'status' => 'required|in:draft,published,cancelled',
    //     ]);

    //     JadwalFasilitasi::create([
    //         'tahun_anggaran_id' => $request->tahun_anggaran_id,
    //         'jenis_dokumen_id' => $request->jenis_dokumen_id,
    //         'nama_kegiatan' => $request->nama_kegiatan,
    //         'tanggal_mulai' => $request->tanggal_mulai,
    //         'tanggal_selesai' => $request->tanggal_selesai,
    //         'batas_permohonan' => $request->batas_permohonan,
    //         'keterangan' => $request->keterangan,
    //         'status' => $request->status,
    //         'created_by' => auth()->id(),
    //     ]);

    //     return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil ditambahkan.');
    // }
    public function store(Request $request)
{
    $request->validate([
        'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
        'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
        'nama_kegiatan' => 'required|string|max:200', // Pastiin required
        'tanggal_mulai' => 'required|date',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'batas_permohonan' => 'required|date|after_or_equal:tanggal_mulai', // Pastiin required
        'keterangan' => 'nullable|string', // Boleh nullable
        'status' => 'required|in:draft,published,cancelled',
    ]);

    JadwalFasilitasi::create([
        'tahun_anggaran_id' => $request->tahun_anggaran_id,
        'jenis_dokumen_id' => $request->jenis_dokumen_id,
        'nama_kegiatan' => $request->nama_kegiatan, // Harus diisi
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'batas_permohonan' => $request->batas_permohonan, // Harus diisi
        'keterangan' => $request->keterangan, // Boleh null
        'status' => $request->status,
        'created_by' => auth()->id(),
    ]);

    return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil ditambahkan.');
}

    public function show(JadwalFasilitasi $jadwal)
    {
        return view('jadwal-fasilitasi.show', compact('jadwal'));
    }

    public function edit(JadwalFasilitasi $jadwal)
    {
        $tahunAnggaran = TahunAnggaran::where('is_active', true)->get();
        $jenisDokumen = JenisDokumen::where('is_active', true)->get();
        
        return view('jadwal-fasilitasi.edit', compact('jadwal', 'tahunAnggaran', 'jenisDokumen'));
    }

    public function update(Request $request, JadwalFasilitasi $jadwal)
    {
        $request->validate([
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
            'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'nama_kegiatan' => 'required|string|max:200',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'batas_permohonan' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,published,cancelled',
        ]);
    
        $jadwal->update([
            'tahun_anggaran_id' => $request->tahun_anggaran_id,
            'jenis_dokumen_id' => $request->jenis_dokumen_id,
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'batas_permohonan' => $request->batas_permohonan,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);
    
        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil diperbarui.');
    }

    public function destroy(JadwalFasilitasi $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil dihapus.');
    }

    public function publish(JadwalFasilitasi $jadwal)
    {
        $jadwal->update(['status' => 'published']);
        return redirect()->back()->with('success', 'Jadwal berhasil dipublikasikan.');
    }

    public function cancel(JadwalFasilitasi $jadwal)
    {
        $jadwal->update(['status' => 'cancelled']);
        return redirect()->back()->with('success', 'Jadwal berhasil dibatalkan.');
    }
}