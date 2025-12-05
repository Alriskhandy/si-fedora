<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalFasilitasi;
use App\Models\Permohonan;

class PemohonJadwalController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalFasilitasi::with(['permohonan.kabupatenKota'])
            ->where('tanggal_pelaksanaan', '>=', now());

        // Filter by jenis dokumen
        if ($request->filled('jenis_dokumen')) {
            $query->whereHas('permohonan', function($q) use ($request) {
                $q->where('jenis_dokumen', $request->jenis_dokumen);
            });
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->whereHas('permohonan', function($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        // Filter by kabupaten/kota (untuk user pemohon)
        if (auth()->user()->kabupaten_kota_id) {
            $query->whereHas('permohonan', function($q) {
                $q->where('kab_kota_id', auth()->user()->kabupaten_kota_id);
            });
        }

        $jadwalList = $query->orderBy('tanggal_pelaksanaan', 'asc')->paginate(10);

        // Data untuk filter
        $filterOptions = [
            'jenisDokumen' => ['perda' => 'PERDA', 'perkada' => 'PERKADA'],
            'tahunList' => Permohonan::distinct()->pluck('tahun')->sort()->values(),
        ];

        return view('pemohon.jadwal.index', compact('jadwalList', 'filterOptions'));
    }

    public function show(JadwalFasilitasi $jadwal)
    {
        $jadwal->load(['permohonan.kabupatenKota']);

        return view('pemohon.jadwal.show', compact('jadwal'));
    }
}
