<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalFasilitasi;
use App\Models\JenisDokumen;
use App\Models\Permohonan;
use App\Models\TahunAnggaran;

class PemohonJadwalController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalFasilitasi::with(['tahunAnggaran', 'jenisDokumen'])
            ->where('status', 'published');

        // Filter by jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }

        // Filter by tahun anggaran
        if ($request->filled('tahun_anggaran_id')) {
            $query->where('tahun_anggaran_id', $request->tahun_anggaran_id);
        }

        // Filter by status (aktif/expired)
        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'aktif') {
                $query->where('batas_permohonan', '>=', now());
            } elseif ($request->filter_status === 'expired') {
                $query->where('batas_permohonan', '<', now());
            }
        } else {
            // Default: hanya tampilkan jadwal aktif
            $query->where('batas_permohonan', '>=', now());
        }

        $jadwalList = $query->orderBy('batas_permohonan', 'asc')->paginate(10);

        // Data untuk filter
        $filterOptions = [
            'jenisDokumen' => JenisDokumen::where('is_active', true)->get(),
            'tahunAnggaran' => TahunAnggaran::where('is_active', true)->get(),
        ];

        return view('pemohon.jadwal.index', compact('jadwalList', 'filterOptions'));
    }

    public function show(JadwalFasilitasi $jadwal)
    {
        $jadwal->load(['tahunAnggaran', 'jenisDokumen']);

        // Cek apakah user sudah punya permohonan untuk jadwal ini
        $existingPermohonan = Permohonan::where('created_by', auth()->id())
            ->where('jadwal_fasilitasi_id', $jadwal->id)
            ->first();

        return view('pemohon.jadwal.show', compact('jadwal', 'existingPermohonan'));
    }
}
