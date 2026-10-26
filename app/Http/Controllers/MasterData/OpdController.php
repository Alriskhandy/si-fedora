<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpdRequest;
use App\Models\MasterUrusan;
use App\Models\Opd;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Daftar OPD dengan pencarian (kode, nama, singkatan) dan filter jenis, rumpun, status,
     * serta ada/tidaknya urusan masukan. Nilai filter di luar daftar referensi diabaikan.
     */
    public function index(Request $request): View
    {
        $filter = [
            'q' => trim((string) $request->query('q')),
            'jenis' => in_array($request->query('jenis'), Opd::JENIS_OPD, true) ? $request->query('jenis') : null,
            'rumpun' => in_array($request->query('rumpun'), MasterUrusan::rumpunList(), true) ? $request->query('rumpun') : null,
            'status' => in_array($request->query('status'), Opd::STATUS, true) ? $request->query('status') : null,
            'urusan' => in_array($request->query('urusan'), ['ada', 'belum'], true) ? $request->query('urusan') : null,
        ];

        $opd = Opd::query()
            ->with(['urusan:id,kode_urusan,nama_urusan', 'induk:id,kode_opd,singkatan'])
            ->withCount('users')
            ->when($filter['q'] !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->whereLike('kode_opd', "%{$filter['q']}%")
                ->orWhereLike('nama_opd', "%{$filter['q']}%")
                ->orWhereLike('singkatan', "%{$filter['q']}%")))
            ->when($filter['jenis'], fn (Builder $query, string $jenis) => $query->where('jenis_opd', $jenis))
            ->when($filter['rumpun'], fn (Builder $query, string $rumpun) => $query->where('rumpun_urusan', $rumpun))
            ->when($filter['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filter['urusan'] === 'ada', fn (Builder $query) => $query->has('urusan'))
            ->when($filter['urusan'] === 'belum', fn (Builder $query) => $query->doesntHave('urusan'))
            ->orderBy('kode_opd')
            ->paginate(15)
            ->withQueryString();

        $totalOpd = Opd::count();

        return view('pages.master-data.opd.index', compact('opd', 'filter', 'totalOpd'));
    }

    /**
     * Form tambah OPD.
     */
    public function create(): View
    {
        return view('pages.master-data.opd.create', ['opd' => null]);
    }

    /**
     * Simpan OPD baru.
     */
    public function store(OpdRequest $request): RedirectResponse
    {
        Opd::create($request->validated());

        return redirect()->route('opd.index')->with('success', 'OPD berhasil ditambahkan.');
    }

    /**
     * Form ubah OPD.
     */
    public function edit(Opd $opd): View
    {
        return view('pages.master-data.opd.edit', ['opd' => $opd->load('urusan')]);
    }

    /**
     * Simpan perubahan OPD.
     */
    public function update(OpdRequest $request, Opd $opd): RedirectResponse
    {
        $opd->update($request->validated());

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
}
