<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterUrusanRequest;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterUrusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Master urusan pemerintahan (kamus data urusan): kode X.XX, kelompok 1–8, OPD pengampu,
 * dan penanda tampil di bagian urusan hasil fasilitasi.
 *
 * Akses: master.urusan.manage.
 * View: resources/views/pages/master-data/urusan
 */
class MasterUrusanController extends Controller
{
    /**
     * Daftar urusan per kelompok, urut kode urusan.
     */
    public function index(Request $request): View
    {
        $urusan = MasterUrusan::query()
            ->when($request->filled('kelompok'), fn ($query) => $query->where('kode_kelompok', $request->kelompok))
            ->orderBy('kode_urusan')
            ->get();

        return view('pages.master-data.urusan.index', compact('urusan'));
    }

    /**
     * Form tambah urusan.
     */
    public function create(): View
    {
        return view('pages.master-data.urusan.create');
    }

    /**
     * Simpan urusan baru.
     */
    public function store(MasterUrusanRequest $request): RedirectResponse
    {
        MasterUrusan::create($request->validated());

        return redirect()->route('master-urusan.index')->with('success', 'Urusan berhasil ditambahkan.');
    }

    /**
     * Form ubah urusan.
     */
    public function edit(MasterUrusan $masterUrusan): View
    {
        return view('pages.master-data.urusan.edit', compact('masterUrusan'));
    }

    /**
     * Simpan perubahan urusan.
     */
    public function update(MasterUrusanRequest $request, MasterUrusan $masterUrusan): RedirectResponse
    {
        $masterUrusan->update($request->validated());

        return redirect()->route('master-urusan.index')->with('success', 'Urusan berhasil diperbarui.');
    }

    /**
     * Aktifkan/nonaktifkan urusan di bagian urusan hasil fasilitasi.
     */
    public function toggleHasilFasilitasi(MasterUrusan $masterUrusan): RedirectResponse
    {
        $masterUrusan->update(['tampil_hasil_fasilitasi' => ! $masterUrusan->tampil_hasil_fasilitasi]);

        return redirect()->back()->with('success', sprintf(
            'Urusan %s %s di hasil fasilitasi.',
            $masterUrusan->nama_urusan,
            $masterUrusan->tampil_hasil_fasilitasi ? 'ditampilkan' : 'tidak lagi ditampilkan',
        ));
    }

    /**
     * Hapus urusan. Urusan yang sudah dipakai masukan hasil fasilitasi tidak bisa dihapus (nonaktifkan saja).
     */
    public function destroy(MasterUrusan $masterUrusan): RedirectResponse
    {
        if (HasilFasilitasiUrusan::where('master_urusan_id', $masterUrusan->id)->exists()) {
            return redirect()->route('master-urusan.index')
                ->with('error', 'Urusan sudah dipakai pada masukan hasil fasilitasi. Ubah statusnya menjadi Tidak Aktif sebagai gantinya.');
        }

        $masterUrusan->delete();

        return redirect()->route('master-urusan.index')->with('success', 'Urusan berhasil dihapus.');
    }
}
