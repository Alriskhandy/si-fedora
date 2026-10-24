<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterTahapan;
use Illuminate\Http\Request;

/**
 * Master tahapan fasilitasi. Hanya bisa diubah (nama, urutan); daftar tahapan tetap 7.
 *
 * Akses: master.tahapan.manage.
 * View: resources/views/pages/master-data/tahapan
 */
class MasterTahapanController extends Controller
{
    /**
     * Daftar tahapan sesuai urutan.
     */
    public function index()
    {
        $tahapan = MasterTahapan::orderBy('urutan')->get();

        return view('pages.master-data.tahapan.index', compact('tahapan'));
    }

    /**
     * Form ubah tahapan.
     */
    public function edit(MasterTahapan $masterTahapan)
    {
        return view('pages.master-data.tahapan.edit', compact('masterTahapan'));
    }

    /**
     * Simpan perubahan tahapan.
     */
    public function update(Request $request, MasterTahapan $masterTahapan)
    {
        $validated = $request->validate([
            'nama_tahapan' => 'required|string|max:255',
            'urutan' => 'required|integer|min:1',
        ]);

        $masterTahapan->update($validated);

        return redirect()->route('master-tahapan.index')
            ->with('success', 'Tahapan berhasil diperbarui.');
    }
}
