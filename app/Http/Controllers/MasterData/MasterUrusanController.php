<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterUrusanRequest;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterUrusan;
use Illuminate\Database\Eloquent\Builder;
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
     * Daftar urusan per kelompok, urut kode urusan, dengan pencarian (kode, nama, OPD pengampu) dan
     * filter kelompok, status, tampil di hasil fasilitasi, serta ada/tidaknya OPD pengampu.
     * Nilai filter di luar daftar referensi diabaikan.
     */
    public function index(Request $request): View
    {
        $filter = [
            'q' => trim((string) $request->query('q')),
            'kelompok' => array_key_exists((int) $request->query('kelompok'), MasterUrusan::KELOMPOK) ? (string) (int) $request->query('kelompok') : null,
            'status' => in_array($request->query('status'), MasterUrusan::STATUS, true) ? $request->query('status') : null,
            'hasil' => in_array($request->query('hasil'), ['tampil', 'tidak'], true) ? $request->query('hasil') : null,
            'pengampu' => in_array($request->query('pengampu'), ['ada', 'belum'], true) ? $request->query('pengampu') : null,
        ];

        $urusan = MasterUrusan::query()
            ->when($filter['q'] !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->whereLike('kode_urusan', "%{$filter['q']}%")
                ->orWhereLike('nama_urusan', "%{$filter['q']}%")
                ->orWhereLike('kode_opd_pengampu', "%{$filter['q']}%")
                ->orWhereLike('opd_pengampu', "%{$filter['q']}%")))
            ->when($filter['kelompok'], fn (Builder $query, string $kelompok) => $query->where('kode_kelompok', $kelompok))
            ->when($filter['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filter['hasil'], fn (Builder $query, string $hasil) => $query->where('tampil_hasil_fasilitasi', $hasil === 'tampil'))
            ->when($filter['pengampu'] === 'ada', fn (Builder $query) => $query->whereNotNull('kode_opd_pengampu'))
            ->when($filter['pengampu'] === 'belum', fn (Builder $query) => $query->whereNull('kode_opd_pengampu'))
            ->orderBy('kode_urusan')
            ->paginate(15)
            ->withQueryString();

        $totalUrusan = MasterUrusan::count();

        return view('pages.master-data.urusan.index', compact('urusan', 'filter', 'totalUrusan'));
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
