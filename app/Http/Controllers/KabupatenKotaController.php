<?php

namespace App\Http\Controllers;

use App\Models\KabupatenKota;
use Illuminate\Http\Request;

class KabupatenKotaController extends Controller
{
    public function index(Request $request)
    {
        $query = KabupatenKota::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode', 'like', '%' . $request->search . '%');
        }

        $kabupatenKota = $query->latest()->paginate(10);

        return view('kabupaten-kota.index', compact('kabupatenKota'));
    }

    public function create()
    {
        return view('kabupaten-kota.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:kabupaten_kota,kode',
            'nama' => 'required|string|max:100',
            'jenis' => 'required|in:kabupaten,kota',
            'kepala_daerah' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        KabupatenKota::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'kepala_daerah' => $request->kepala_daerah,
            'email' => $request->email,
            'telepon' => $request->telepon,
            'alamat' => $request->alamat,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('kabupaten-kota.index')->with('success', 'Kabupaten/Kota berhasil ditambahkan.');
    }

    public function show(KabupatenKota $kabupatenKota)
    {
        return view('kabupaten-kota.show', compact('kabupatenKota'));
    }

    public function edit(KabupatenKota $kabupatenKota)
    {
        return view('kabupaten-kota.edit', compact('kabupatenKota'));
    }

    public function update(Request $request, KabupatenKota $kabupatenKota)
    {
        $request->validate([
            'kode' => 'required|string|unique:kabupaten_kota,kode,' . $kabupatenKota->id,
            'nama' => 'required|string|max:100',
            'jenis' => 'required|in:kabupaten,kota',
            'kepala_daerah' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $kabupatenKota->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'kepala_daerah' => $request->kepala_daerah,
            'email' => $request->email,
            'telepon' => $request->telepon,
            'alamat' => $request->alamat,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('kabupaten-kota.index')->with('success', 'Kabupaten/Kota berhasil diperbarui.');
    }

    public function destroy(KabupatenKota $kabupatenKota)
    {
        $kabupatenKota->delete();
        return redirect()->route('kabupaten-kota.index')->with('success', 'Kabupaten/Kota berhasil dihapus.');
    }
}