<?php

namespace App\Http\Controllers;

use App\Models\MasterTahapan;
use Illuminate\Http\Request;

class MasterTahapanController extends Controller
{
    public function index()
    {
        $tahapan = MasterTahapan::orderBy('urutan')->get();

        return view('pages.master-tahapan.index', compact('tahapan'));
    }

    public function edit(MasterTahapan $masterTahapan)
    {
        return view('pages.master-tahapan.edit', compact('masterTahapan'));
    }

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
