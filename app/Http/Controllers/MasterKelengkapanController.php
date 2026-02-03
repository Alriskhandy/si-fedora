<?php

namespace App\Http\Controllers;

use App\Models\MasterKelengkapanVerifikasi;
use App\Models\MasterJenisDokumen;
use Illuminate\Http\Request;

class MasterKelengkapanController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterKelengkapanVerifikasi::with(['jenisDokumen']);
        
        // Filter berdasarkan jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }
        
        $kelengkapan = $query->orderBy('jenis_dokumen_id')
                             ->orderBy('urutan')
                             ->get();
        
        $jenisDokumen = MasterJenisDokumen::orderBy('nama')->get();
        
        return view('pages.master-kelengkapan.index', compact('kelengkapan', 'jenisDokumen'));
    }

    public function create()
    {
        $jenisDokumen = MasterJenisDokumen::orderBy('nama')->get();
        
        return view('pages.master-kelengkapan.create', compact('jenisDokumen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'jenis_dokumen_id' => 'required|exists:master_jenis_dokumen,id',
            'deskripsi' => 'nullable|string',
            'wajib' => 'required|boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);

        MasterKelengkapanVerifikasi::create($validated);

        return redirect()->route('master-kelengkapan.index')
            ->with('success', 'Kelengkapan verifikasi berhasil ditambahkan.');
    }

    public function edit(MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $jenisDokumen = MasterJenisDokumen::orderBy('nama')->get();
        
        return view('pages.master-kelengkapan.edit', compact('masterKelengkapan', 'jenisDokumen'));
    }

    public function update(Request $request, MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $validated = $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'jenis_dokumen_id' => 'required|exists:master_jenis_dokumen,id',
            'deskripsi' => 'nullable|string',
            'wajib' => 'required|boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);

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
