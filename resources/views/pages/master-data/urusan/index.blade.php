@extends('layouts.app')

@section('title', 'Master Urusan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} alert-dismissible" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
    @endforeach

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0">Daftar Urusan Pemerintahan</h5>
            <div class="d-flex gap-2">
                <form method="GET">
                    <select name="kelompok" class="form-select" onchange="this.form.submit()" aria-label="Filter kelompok">
                        <option value="">Semua Kelompok</option>
                        @foreach (App\Models\MasterUrusan::KELOMPOK as $kode => $item)
                            <option value="{{ $kode }}" @selected((string) request('kelompok') === (string) $kode)>{{ $kode }}. {{ $item['rumpun'] }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('master-urusan.create') }}" class="btn btn-primary text-nowrap">
                    <i class="bx bx-plus me-1"></i> Tambah Urusan
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Urusan</th>
                        <th>Rumpun</th>
                        <th>OPD Pengampu</th>
                        <th>Status</th>
                        <th>Hasil Fasilitasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php($kelompokSaatIni = null)
                    @forelse ($urusan as $item)
                        @if ($kelompokSaatIni !== $item->kode_kelompok)
                            @php($kelompokSaatIni = $item->kode_kelompok)
                            <tr class="table-secondary">
                                <td colspan="7" class="fw-bold">{{ $item->kode_kelompok }}. {{ $item->nama_kelompok }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-nowrap">{{ $item->kode_urusan }}</td>
                            <td>{{ $item->nama_urusan }}</td>
                            <td>{{ $item->rumpun_urusan }}</td>
                            <td>
                                {{ $item->opd_pengampu ?? '—' }}
                                @if ($item->kode_opd_pengampu)
                                    <small class="d-block text-muted">{{ $item->kode_opd_pengampu }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $item->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $item->status }}</span>
                                <small class="d-block text-muted">{{ $item->tanggal_update?->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <form action="{{ route('master-urusan.toggle-hasil-fasilitasi', $item) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $item->tampil_hasil_fasilitasi ? 'btn-success' : 'btn-outline-secondary' }}"
                                        title="Klik untuk {{ $item->tampil_hasil_fasilitasi ? 'menonaktifkan' : 'mengaktifkan' }}">
                                        <i class="bx {{ $item->tampil_hasil_fasilitasi ? 'bx-check' : 'bx-x' }} me-1"></i>{{ $item->tampil_hasil_fasilitasi ? 'Tampil' : 'Tidak tampil' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('master-urusan.edit', $item) }}" class="btn btn-sm btn-primary" title="Ubah">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                                <form action="{{ route('master-urusan.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus urusan ini?')">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
