<?php

namespace App\Http\Controllers;

use App\Models\TahunAnggaran;
use Illuminate\Http\Request;

class TahunAnggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = TahunAnggaran::query();

        if ($request->filled('search')) {
            $query->where('tahun', 'like', '%' . $request->search . '%')
                  ->orWhere('nama', 'like', '%' . $request->search . '%');
        }

        $tahunAnggaran = $query->latest()->paginate(10);

        return view('tahun-anggaran.index', compact('tahunAnggaran'));
    }

    public function create()
    {
        return view('tahun-anggaran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|unique:tahun_anggaran,tahun',
            'nama' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        TahunAnggaran::create([
            'tahun' => $request->tahun,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('tahun-anggaran.index')->with('success', 'Tahun anggaran berhasil ditambahkan.');
    }

    public function show(TahunAnggaran $tahunAnggaran)
    {
        return view('tahun-anggaran.show', compact('tahunAnggaran'));
    }

    public function edit(TahunAnggaran $tahunAnggaran)
    {
        return view('tahun-anggaran.edit', compact('tahunAnggaran'));
    }

    public function update(Request $request, TahunAnggaran $tahunAnggaran)
    {
        $request->validate([
            'tahun' => 'required|integer|unique:tahun_anggaran,tahun,' . $tahunAnggaran->id,
            'nama' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $tahunAnggaran->update([
            'tahun' => $request->tahun,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('tahun-anggaran.index')->with('success', 'Tahun anggaran berhasil diperbarui.');
    }

    public function destroy(TahunAnggaran $tahunAnggaran)
    {
        $tahunAnggaran->delete();
        return redirect()->route('tahun-anggaran.index')->with('success', 'Tahun anggaran berhasil dihapus.');
    }
}