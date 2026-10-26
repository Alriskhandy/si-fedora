<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\KabupatenKota;
use Illuminate\Http\Request;

/**
 * Master data kabupaten/kota.
 *
 * Akses: master.kabkota.manage.
 * View: resources/views/pages/master-data/kabupaten-kota
 */
class KabupatenKotaController extends Controller
{
    /**
     * Daftar kabupaten/kota dengan pencarian nama atau kode.
     */
    public function index(Request $request)
    {
        $query = KabupatenKota::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%'.$request->search.'%')
                ->orWhere('kode', 'like', '%'.$request->search.'%');
        }

        $kabupatenKota = $query->latest()->paginate(10);

        return view('pages.master-data.kabupaten-kota.index', compact('kabupatenKota'));
    }

    /**
     * Form tambah kabupaten/kota.
     */
    public function create()
    {
        return view('pages.master-data.kabupaten-kota.create');
    }

    /**
     * Simpan kabupaten/kota baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:kabupaten_kota,kode',
            'nama' => 'required|string|max:100',
            'jenis' => 'required|in:kabupaten,kota',
            'is_active' => 'boolean',
        ]);

        KabupatenKota::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('kabupaten-kota.index')->with('success', 'Kabupaten/Kota berhasil ditambahkan.');
    }

    /**
     * Detail kabupaten/kota.
     */
    public function show(KabupatenKota $kabupatenKota)
    {
        return view('pages.master-data.kabupaten-kota.show', compact('kabupatenKota'));
    }

    /**
     * Form ubah kabupaten/kota.
     */
    public function edit(KabupatenKota $kabupatenKota)
    {
        return view('pages.master-data.kabupaten-kota.edit', compact('kabupatenKota'));
    }

    /**
     * Simpan perubahan kabupaten/kota.
     */
    public function update(Request $request, KabupatenKota $kabupatenKota)
    {
        $request->validate([
            'kode' => 'required|string|unique:kabupaten_kota,kode,'.$kabupatenKota->id,
            'nama' => 'required|string|max:100',
            'jenis' => 'required|in:kabupaten,kota',
            'is_active' => 'boolean',
        ]);

        $kabupatenKota->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('kabupaten-kota.index')->with('success', 'Kabupaten/Kota berhasil diperbarui.');
    }

    /**
     * Hapus kabupaten/kota.
     */
    public function destroy(KabupatenKota $kabupatenKota)
    {
        $kabupatenKota->delete();

        return redirect()->route('kabupaten-kota.index')->with('success', 'Kabupaten/Kota berhasil dihapus.');
    }
}
