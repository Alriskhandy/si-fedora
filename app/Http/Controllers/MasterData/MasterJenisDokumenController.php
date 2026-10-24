<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterJenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Master jenis dokumen perencanaan (RPJPD, RPJMD, RKPD).
 *
 * Akses: master.jenis-dokumen.manage.
 * View: resources/views/pages/master-data/jenis-dokumen
 */
class MasterJenisDokumenController extends Controller
{
    /**
     * Daftar jenis dokumen.
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
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        $jenisDokumens = $query->orderBy('nama')->paginate(20);

        return view('pages.master-data.jenis-dokumen.index', compact('jenisDokumens', 'request'));
    }

    /**
     * Form tambah jenis dokumen.
     */
    public function create()
    {
        return view('pages.master-data.jenis-dokumen.create');
    }

    /**
     * Simpan jenis dokumen baru.
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
            Log::error('Error creating jenis dokumen: '.$e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menambahkan jenis dokumen: '.$e->getMessage()]);
        }
    }

    /**
     * Form ubah jenis dokumen.
     */
    public function edit(MasterJenisDokumen $masterJenisDokuman)
    {
        return view('pages.master-data.jenis-dokumen.edit', compact('masterJenisDokuman'));
    }

    /**
     * Simpan perubahan jenis dokumen.
     */
    public function update(Request $request, MasterJenisDokumen $masterJenisDokuman)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:master_jenis_dokumen,nama,'.$masterJenisDokuman->id,
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
            Log::error('Error updating jenis dokumen: '.$e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal mengupdate jenis dokumen: '.$e->getMessage()]);
        }
    }

    /**
     * Hapus jenis dokumen.
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
            Log::error('Error deleting jenis dokumen: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal menghapus jenis dokumen: '.$e->getMessage()]);
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(MasterJenisDokumen $masterJenisDokuman)
    {
        try {
            $masterJenisDokuman->update(['status' => ! $masterJenisDokuman->status]);

            return redirect()
                ->route('master-jenis-dokumen.index')
                ->with('success', 'Status berhasil diubah.');
        } catch (\Exception $e) {
            Log::error('Error toggling status: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal mengubah status: '.$e->getMessage()]);
        }
    }
}
