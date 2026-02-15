<?php

namespace App\Http\Controllers;

use App\Models\MasterJenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterJenisDokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MasterJenisDokumen::withCount('babs');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === '1') {
                $query->active();
            } elseif ($request->status === '0') {
                $query->inactive();
            }
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $jenisDokumens = $query->orderBy('nama')->paginate(20);

        return view('pages.master-jenis-dokumen.index', compact('jenisDokumens', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.master-jenis-dokumen.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:master_jenis_dokumen,nama',
            'status' => 'boolean',
        ], [
            'nama.required' => 'Nama jenis dokumen wajib diisi.',
            'nama.unique' => 'Nama jenis dokumen sudah digunakan.',
        ]);

        try {
            MasterJenisDokumen::create($validated);

            return redirect()
                ->route('master-jenis-dokumen.index')
                ->with('success', 'Jenis Dokumen berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating jenis dokumen: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menambahkan jenis dokumen: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterJenisDokumen $masterJenisDokuman)
    {
        return view('pages.master-jenis-dokumen.edit', compact('masterJenisDokuman'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterJenisDokumen $masterJenisDokuman)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:master_jenis_dokumen,nama,' . $masterJenisDokuman->id,
            'status' => 'boolean',
        ], [
            'nama.required' => 'Nama jenis dokumen wajib diisi.',
            'nama.unique' => 'Nama jenis dokumen sudah digunakan.',
        ]);

        try {
            $masterJenisDokuman->update($validated);

            return redirect()
                ->route('master-jenis-dokumen.index')
                ->with('success', 'Jenis Dokumen berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Error updating jenis dokumen: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal mengupdate jenis dokumen: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterJenisDokumen $masterJenisDokuman)
    {
        try {
            // Check if has babs
            if ($masterJenisDokuman->babs()->exists()) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus jenis dokumen yang memiliki bab terkait.']);
            }

            $masterJenisDokuman->delete();

            return redirect()
                ->route('master-jenis-dokumen.index')
                ->with('success', 'Jenis Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting jenis dokumen: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Gagal menghapus jenis dokumen: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(MasterJenisDokumen $masterJenisDokuman)
    {
        try {
            $masterJenisDokuman->update(['status' => !$masterJenisDokuman->status]);

            return redirect()
                ->route('master-jenis-dokumen.index')
                ->with('success', 'Status berhasil diubah.');
        } catch (\Exception $e) {
            Log::error('Error toggling status: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Gagal mengubah status: ' . $e->getMessage()]);
        }
    }
}
