<?php

namespace App\Http\Controllers;

use App\Models\MasterKelengkapanVerifikasi;
use Illuminate\Http\Request;

class MasterKelengkapanController extends Controller
{
    public function index()
    {
        $kelengkapan = MasterKelengkapanVerifikasi::orderBy('id')->get();
        return view('master-kelengkapan.index', compact('kelengkapan'));
    }

    public function create()
    {
        return view('master-kelengkapan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'wajib' => 'required|boolean',
        ]);

        MasterKelengkapanVerifikasi::create($validated);

        return redirect()->route('master-kelengkapan.index')
            ->with('success', 'Kelengkapan verifikasi berhasil ditambahkan.');
    }

    public function edit(MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        return view('master-kelengkapan.edit', compact('masterKelengkapan'));
    }

    public function update(Request $request, MasterKelengkapanVerifikasi $masterKelengkapan)
    {
        $validated = $request->validate([
            'nama_dokumen' => 'required|string|max:255',
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
