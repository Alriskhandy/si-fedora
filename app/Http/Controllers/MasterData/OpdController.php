<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpdRequest;
use App\Models\MasterUrusan;
use App\Models\Opd;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Master OPD lingkungan Provinsi (kamus data OPD) beserta urusan yang boleh diberi masukan.
 * Dipakai saat Admin membuat akun pengguna ber-role opd.
 *
 * Akses: master.opd.manage.
 * View: resources/views/pages/master-data/opd
 */
class OpdController extends Controller
{
    /**
     * Daftar OPD.
     */
    public function index(): View
    {
        $opd = Opd::with('urusan:id,nama_urusan')->withCount('users')->orderBy('kode_opd')->get();

        return view('pages.master-data.opd.index', compact('opd'));
    }

    /**
     * Form tambah OPD.
     */
    public function create(): View
    {
        return view('pages.master-data.opd.create', ['urusanList' => $this->urusanList()]);
    }

    /**
     * Simpan OPD baru.
     */
    public function store(OpdRequest $request): RedirectResponse
    {
        $opd = Opd::create($request->safe()->except('urusan'));
        $opd->urusan()->sync($request->validated('urusan', []));

        return redirect()->route('opd.index')->with('success', 'OPD berhasil ditambahkan.');
    }

    /**
     * Form ubah OPD.
     */
    public function edit(Opd $opd): View
    {
        return view('pages.master-data.opd.edit', ['opd' => $opd, 'urusanList' => $this->urusanList()]);
    }

    /**
     * Simpan perubahan OPD.
     */
    public function update(OpdRequest $request, Opd $opd): RedirectResponse
    {
        $opd->update($request->safe()->except('urusan'));
        $opd->urusan()->sync($request->validated('urusan', []));

        return redirect()->route('opd.index')->with('success', 'OPD berhasil diperbarui.');
    }

    /**
     * Hapus OPD. OPD yang masih punya akun pengguna tidak bisa dihapus (nonaktifkan saja).
     */
    public function destroy(Opd $opd): RedirectResponse
    {
        if ($opd->users()->withTrashed()->exists()) {
            return redirect()->route('opd.index')->with('error', 'OPD masih memiliki akun pengguna. Nonaktifkan OPD sebagai gantinya.');
        }

        $opd->delete();

        return redirect()->route('opd.index')->with('success', 'OPD berhasil dihapus.');
    }

    /**
     * Daftar urusan untuk pilihan bidang OPD.
     *
     * @return Collection<int, MasterUrusan>
     */
    private function urusanList()
    {
        return MasterUrusan::untukHasilFasilitasi()->orderBy('kode_urusan')->get(['id', 'kode_urusan', 'nama_urusan']);
    }
}
