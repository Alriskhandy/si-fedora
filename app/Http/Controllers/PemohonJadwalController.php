<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalFasilitasi;
use App\Models\MasterJenisDokumen;
use App\Models\Permohonan;

class PemohonJadwalController extends Controller
{
    public function index(Request $request)
    {
        // Tampilkan jadwal yang published dan masih aktif (batas permohonan belum lewat)
        $query = JadwalFasilitasi::where('status', 'published')
            ->where('batas_permohonan', '>=', now())->with(['jenisDokumen']);

        // Filter by jenis dokumen (filter langsung di jadwal, bukan lewat permohonan)
        if ($request->filled('jenis_dokumen')) {
            $query->where('jenis_dokumen', $request->jenis_dokumen);
        }

        // Filter by tahun (filter langsung di jadwal)
        if ($request->filled('tahun')) {
            $query->where('tahun_anggaran', $request->tahun);
        }

        $jadwalList = $query->orderBy('batas_permohonan', 'asc')
            ->orderBy('tanggal_mulai', 'asc')
            ->paginate(10);

        // Data untuk filter - ambil dari jadwal yang published
        $filterOptions = [
            'tahunList' => JadwalFasilitasi::where('status', 'published')
                ->distinct()
                ->orderBy('tahun_anggaran', 'desc')
                ->pluck('tahun_anggaran'),
        ];

        $filterJenisDokumen = MasterJenisDokumen::where('status', true)->get();

        return view('pages.pemohon.jadwal.index', compact('jadwalList', 'filterOptions', 'filterJenisDokumen'));
    }

    public function show(JadwalFasilitasi $jadwal)
    {
        // Load permohonan yang terkait dengan jadwal ini (opsional, untuk info)
        $jadwal->load(['permohonan.kabupatenKota']);

        // Check if current user already has permohonan for this jadwal
        $existingPermohonan = null;
        if (auth()->check()) {
            $existingPermohonan = Permohonan::where('jadwal_fasilitasi_id', $jadwal->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('pages.pemohon.jadwal.show', compact('jadwal', 'existingPermohonan'));
    }
}
