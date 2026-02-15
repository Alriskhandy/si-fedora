<?php

namespace App\Http\Controllers;

use App\Models\KabupatenKota;
use Illuminate\Http\Request;
use App\Models\Evaluasi; 

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

        return view('pages.master-kab-kota.index', compact('kabupatenKota'));
    }

    public function create()
    {
        return view('pages.master-kab-kota.create');
    }

    public function evaluasi(KabupatenKota $kabupatenKota)
    {
        $this->authorize('evaluasi.view');
        
        $evaluasi = $kabupatenKota->evaluasi()->latest()->get();
        
        return view('pages.master-kab-kota.evaluasi', [
            'kabupatenKota' => $kabupatenKota,
            'evaluasi' => $evaluasi
        ]);
    }

    public function storeEvaluasi(Request $request, KabupatenKota $kabupatenKota)
    {
        $this->authorize('evaluasi.create');
        
        $validated = $request->validate([
            'aspek' => 'required|string|max:255',
            'nilai' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string'
        ]);
        
        $kabupatenKota->evaluasi()->create($validated);
        
        return redirect()->back()
            ->with('success', 'Evaluasi berhasil ditambahkan');
    }

    public function updateEvaluasi(Request $request, Evaluasi $evaluasi)
    {
        $this->authorize('evaluasi.edit');
        
        $validated = $request->validate([
            'aspek' => 'required|string|max:255',
            'nilai' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string'
        ]);
        
        $evaluasi->update($validated);
        
        return redirect()->back()
            ->with('success', 'Evaluasi berhasil diperbarui');
    }

    public function destroyEvaluasi(Evaluasi $evaluasi)
    {
        $this->authorize('evaluasi.delete');
        
        $evaluasi->delete();
        
        return redirect()->back()
            ->with('success', 'Evaluasi berhasil dihapus');
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
        return view('pages.master-kab-kota.show', compact('kabupatenKota'));
    }

    public function edit(KabupatenKota $kabupatenKota)
    {
        return view('pages.master-kab-kota.edit', compact('kabupatenKota'));
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