<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterUrusan;
use Illuminate\Http\Request;

/**
 * Master urusan pemerintahan.
 *
 * Akses: master.urusan.manage.
 * View: resources/views/pages/master-data/urusan
 */
class MasterUrusanController extends Controller
{
    /**
     * Daftar urusan pemerintahan.
     */
    public function index(Request $request)
    {
        $query = MasterUrusan::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Order by kategori: wajib_dasar, wajib_non_dasar, pilihan
        $urusan = $query->orderByRaw(
            "CASE 
                WHEN kategori = 'wajib_dasar' THEN 1 
                WHEN kategori = 'wajib_non_dasar' THEN 2 
                WHEN kategori = 'pilihan' THEN 3 
            END"
        )->orderBy('urutan')->get();

        return view('pages.master-data.urusan.index', compact('urusan'));
    }

    /**
     * Form tambah urusan.
     */
    public function create()
    {
        return view('pages.master-data.urusan.create');
    }

    /**
     * Simpan urusan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_urusan' => 'required|string|max:255',
            'kategori' => 'required|in:wajib_dasar,wajib_non_dasar,pilihan',
            'urutan' => 'required|integer|min:1',
        ]);

        MasterUrusan::create($validated);

        return redirect()->route('master-urusan.index')
            ->with('success', 'Urusan berhasil ditambahkan.');
    }

    /**
     * Form ubah urusan.
     */
    public function edit(MasterUrusan $masterUrusan)
    {
        return view('pages.master-data.urusan.edit', compact('masterUrusan'));
    }

    /**
     * Simpan perubahan urusan.
     */
    public function update(Request $request, MasterUrusan $masterUrusan)
    {
        $validated = $request->validate([
            'nama_urusan' => 'required|string|max:255',
            'kategori' => 'required|in:wajib_dasar,wajib_non_dasar,pilihan',
            'urutan' => 'required|integer|min:1',
        ]);

        $masterUrusan->update($validated);

        return redirect()->route('master-urusan.index')
            ->with('success', 'Urusan berhasil diperbarui.');
    }

    /**
     * Hapus urusan.
     */
    public function destroy(MasterUrusan $masterUrusan)
    {
        $masterUrusan->delete();

        return redirect()->route('master-urusan.index')
            ->with('success', 'Urusan berhasil dihapus.');
    }
}
