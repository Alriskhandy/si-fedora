<?php

namespace App\Http\Controllers;

use App\Models\MasterUrusan;
use Illuminate\Http\Request;

class MasterUrusanController extends Controller
{
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
        
        return view('master-urusan.index', compact('urusan'));
    }

    public function create()
    {
        return view('master-urusan.create');
    }

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

    public function edit(MasterUrusan $masterUrusan)
    {
        return view('master-urusan.edit', compact('masterUrusan'));
    }

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

    public function destroy(MasterUrusan $masterUrusan)
    {
        $masterUrusan->delete();

        return redirect()->route('master-urusan.index')
            ->with('success', 'Urusan berhasil dihapus.');
    }
}
