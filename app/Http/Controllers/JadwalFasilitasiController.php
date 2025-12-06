<?php

namespace App\Http\Controllers;

use App\Models\JadwalFasilitasi;
use App\Models\Permohonan;
use Illuminate\Http\Request;

class JadwalFasilitasiController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalFasilitasi::with(['permohonan.kabupatenKota']);

        if ($request->filled('search')) {
            $query->whereHas('permohonan', function ($q) use ($request) {
                $q->where('jenis_dokumen', 'like', '%' . $request->search . '%')
                    ->orWhere('tahun', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kabupatenKota', function ($q2) use ($request) {
                        $q2->where('nama', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $jadwalFasilitasi = $query->latest()->paginate(10);

        return view('jadwal-fasilitasi.index', compact('jadwalFasilitasi'));
    }

    public function create()
    {
        $permohonan = Permohonan::with(['kabupatenKota'])
            ->whereNotIn('id', function ($query) {
                $query->select('permohonan_id')->from('jadwal_fasilitasi');
            })
            ->get();

        return view('jadwal-fasilitasi.create', compact('permohonan'));
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
            'permohonan_id' => 'required|exists:permohonan,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'undangan_file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $data = [
            'permohonan_id' => $request->permohonan_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'dibuat_oleh' => auth()->id(),
        ];

        if ($request->hasFile('undangan_file')) {
            $data['undangan_file'] = $request->file('undangan_file')->store('undangan', 'public');
        }

        JadwalFasilitasi::create($data);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil ditambahkan.');
    }

    public function show(JadwalFasilitasi $jadwal)
    {
        return view('jadwal-fasilitasi.show', compact('jadwal'));
    }

    public function edit(JadwalFasilitasi $jadwal)
    {
        $jadwal->load('permohonan.kabupatenKota');

        return view('jadwal-fasilitasi.edit', compact('jadwal'));
    }

    public function update(Request $request, JadwalFasilitasi $jadwal)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'undangan_file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $data = [
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ];

        if ($request->hasFile('undangan_file')) {
            $data['undangan_file'] = $request->file('undangan_file')->store('undangan', 'public');
        }

        $jadwal->update($data);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil diperbarui.');
    }

    public function destroy(JadwalFasilitasi $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('jadwal.index')->with('success', 'Jadwal fasilitasi berhasil dihapus.');
    }
}
