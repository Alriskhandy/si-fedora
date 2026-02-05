<?php

namespace App\Http\Controllers;

use App\Models\MasterKelengkapanVerifikasi;
use App\Models\MasterJenisDokumen;
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

        $kelengkapan = $query->orderBy('urutan')->orderBy('id')->get();
        $jenisDokumen = MasterJenisDokumen::where('status', true)->get();
        
        return view('master-kelengkapan.index', compact('kelengkapan', 'jenisDokumen'));
    }

    public function create()
    {
        $jenisDokumen = MasterJenisDokumen::where('status', true)->get();
        return view('master-kelengkapan.create', compact('jenisDokumen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
            'deskripsi' => 'nullable|string',
            'wajib' => 'required|boolean',
        ]);

        MasterKelengkapanVerifikasi::create($validated);

        return redirect()->route('master-kelengkapan.index')
            ->with('success', 'Kelengkapan verifikasi berhasil ditambahkan.');
    }

    public function edit(MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $jenisDokumen = MasterJenisDokumen::where('status', true)->get();
        return view('master-kelengkapan.edit', compact('masterKelengkapan', 'jenisDokumen'));
    }

    public function update(Request $request, MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $validated = $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
            'deskripsi' => 'nullable|string',
            'wajib' => 'required|boolean',
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
