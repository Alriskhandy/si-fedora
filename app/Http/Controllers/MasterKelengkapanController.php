<?php

namespace App\Http\Controllers;

use App\Models\MasterKelengkapanVerifikasi;
use App\Models\MasterJenisDokumen;
use App\Models\MasterTahapan;
use Illuminate\Http\Request;

class MasterKelengkapanController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterKelengkapanVerifikasi::with('jenisDokumen');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_dokumen', 'ilike', '%' . $search . '%')
                  ->orWhere('deskripsi', 'ilike', '%' . $search . '%');
            });
        }

        // Filter by jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }

        // Filter by status wajib
        if ($request->filled('wajib')) {
            $query->where('wajib', $request->wajib);
        }

        $kelengkapan = $query->orderBy('jenis_dokumen_id')->orderBy('urutan')->get();
        $jenisDokumen = MasterJenisDokumen::where('status', true)->get();
        
        return view('pages.master-kelengkapan.index', compact('kelengkapan', 'jenisDokumen'));
    }

    public function create()
    {
        $jenisDokumen = MasterJenisDokumen::where('status', true)->get();
        return view('pages.master-kelengkapan.create', compact('jenisDokumen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_dokumen'    => 'required|string|max:255',
            'jenis_dokumen_id'=> 'required|exists:master_jenis_dokumen,id',
            'deskripsi'       => 'nullable|string',
            'wajib'           => 'required|boolean',
        ], [
            'jenis_dokumen_id.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen_id.exists'   => 'Jenis dokumen tidak valid.',
        ]);

        // Kelengkapan verifikasi selalu berada di tahapan Permohonan (urutan 1)
        $validated['tahapan_id'] = MasterTahapan::where('urutan', 1)->value('id') ?? 1;

        // Urutan otomatis: setelah data terakhir untuk jenis dokumen yang sama
        $validated['urutan'] = MasterKelengkapanVerifikasi::where('jenis_dokumen_id', $validated['jenis_dokumen_id'])->max('urutan') + 1;

        MasterKelengkapanVerifikasi::create($validated);

        return redirect()->route('master-kelengkapan.index')
            ->with('success', 'Kelengkapan verifikasi berhasil ditambahkan.');
    }

    public function edit(MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $jenisDokumen = MasterJenisDokumen::where('status', true)->get();
        return view('pages.master-kelengkapan.edit', compact('masterKelengkapan', 'jenisDokumen'));
    }

    public function update(Request $request, MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $validated = $request->validate([
            'nama_dokumen'    => 'required|string|max:255',
            'jenis_dokumen_id'=> 'required|exists:master_jenis_dokumen,id',
            'deskripsi'       => 'nullable|string',
            'wajib'           => 'required|boolean',
            'urutan'          => 'required|integer|min:1',
        ], [
            'jenis_dokumen_id.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen_id.exists'   => 'Jenis dokumen tidak valid.',
            'urutan.required'           => 'Urutan wajib diisi.',
            'urutan.min'                => 'Urutan minimal 1.',
        ]);

        // Kelengkapan verifikasi selalu berada di tahapan Permohonan (urutan 1)
        $validated['tahapan_id'] = MasterTahapan::where('urutan', 1)->value('id') ?? 1;

        $masterKelengkapan->update($validated);

        return redirect()->route('master-kelengkapan.index')
            ->with('success', 'Kelengkapan verifikasi berhasil diperbarui.');
    }

    public function destroy(MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $masterKelengkapan->delete();

        return redirect()->route('master-kelengkapan.index')
            ->with('success', 'Kelengkapan verifikasi berhasil dihapus.');
    }
}
