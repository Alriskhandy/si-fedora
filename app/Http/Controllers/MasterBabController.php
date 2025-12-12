<?php

namespace App\Http\Controllers;

use App\Models\MasterBab;
use App\Models\MasterJenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterBabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MasterBab::with(['jenisDokumen']);

        // Filter by jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }

        $babs = $query->orderBy('jenis_dokumen_id')->orderBy('urutan')->orderBy('nama_bab')->get();
        $jenisDokumenList = MasterJenisDokumen::active()->orderBy('nama')->get();

        return view('master-bab.index', compact('babs', 'jenisDokumenList', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jenisDokumenList = MasterJenisDokumen::active()->orderBy('nama')->get();
        $parentBabs = MasterBab::mainBab()->orderBy('urutan')->orderBy('nama_bab')->get();

        return view('master-bab.create', compact('jenisDokumenList', 'parentBabs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bab' => 'required|string|max:255',
            'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
            'parent_id' => 'nullable|exists:master_bab,id',
            'urutan' => 'nullable|integer|min:0',
        ], [
            'nama_bab.required' => 'Nama bab wajib diisi.',
            'jenis_dokumen_id.exists' => 'Jenis dokumen tidak valid.',
            'parent_id.exists' => 'Parent bab tidak valid.',
            'urutan.integer' => 'Urutan harus berupa angka.',
        ]);

        try {
            MasterBab::create($validated);

            return redirect()
                ->route('master-bab.index')
                ->with('success', 'Master Bab berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating master bab: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menambahkan bab: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterBab $masterBab)
    {
        $jenisDokumenList = MasterJenisDokumen::active()->orderBy('nama')->get();
        $parentBabs = MasterBab::mainBab()
            ->where('id', '!=', $masterBab->id)
            ->orderBy('urutan')
            ->orderBy('nama_bab')
            ->get();

        return view('master-bab.edit', compact('masterBab', 'jenisDokumenList', 'parentBabs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterBab $masterBab)
    {
        $validated = $request->validate([
            'nama_bab' => 'required|string|max:255',
            'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
            'parent_id' => 'nullable|exists:master_bab,id',
            'urutan' => 'nullable|integer|min:0',
        ], [
            'nama_bab.required' => 'Nama bab wajib diisi.',
            'jenis_dokumen_id.exists' => 'Jenis dokumen tidak valid.',
            'parent_id.exists' => 'Parent bab tidak valid.',
            'urutan.integer' => 'Urutan harus berupa angka.',
        ]);

        // Prevent self-reference as parent
        if ($validated['parent_id'] == $masterBab->id) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'Bab tidak dapat menjadi parent dari dirinya sendiri.']);
        }

        try {
            $masterBab->update($validated);

            return redirect()
                ->route('master-bab.index')
                ->with('success', 'Master Bab berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Error updating master bab: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal mengupdate bab: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterBab $masterBab)
    {
        try {
            // Check if has children
            if ($masterBab->children()->exists()) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus bab yang memiliki sub-bab.']);
            }

            $masterBab->delete();

            return redirect()
                ->route('master-bab.index')
                ->with('success', 'Master Bab berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting master bab: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Gagal menghapus bab: ' . $e->getMessage()]);
        }
    }
}
