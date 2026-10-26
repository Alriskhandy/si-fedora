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
            <div>
                <h5 class="card-title mb-0">Daftar Urusan Pemerintahan</h5>
                <small class="text-muted">Hanya urusan berstatus Aktif dan bertanda "Tampil" yang muncul di bagian urusan hasil fasilitasi.</small>
            </div>
            <a href="{{ route('master-urusan.create') }}" class="btn btn-primary text-nowrap">
                <i class="bx bx-plus me-1"></i> Tambah Urusan
            </a>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('master-urusan.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-lg-3">
                    <label class="form-label small mb-1" for="q">Cari</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="search" class="form-control" id="q" name="q" value="{{ $filter['q'] }}"
                            placeholder="Kode, nama urusan, atau OPD pengampu">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label small mb-1" for="kelompok">Kelompok</label>
                    <select class="form-select" id="kelompok" name="kelompok">
                        <option value="">Semua</option>
                        @foreach (App\Models\MasterUrusan::KELOMPOK as $kode => $item)
                            <option value="{{ $kode }}" @selected($filter['kelompok'] === (string) $kode)>{{ $kode }}. {{ $item['rumpun'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-1">
                    <label class="form-label small mb-1" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua</option>
                        @foreach (App\Models\MasterUrusan::STATUS as $status)
                            <option value="{{ $status }}" @selected($filter['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label small mb-1" for="hasil">Hasil Fasilitasi</label>
                    <select class="form-select" id="hasil" name="hasil">
                        <option value="">Semua</option>
                        <option value="tampil" @selected($filter['hasil'] === 'tampil')>Tampil</option>
                        <option value="tidak" @selected($filter['hasil'] === 'tidak')>Tidak tampil</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label small mb-1" for="pengampu">OPD Pengampu</label>
                    <select class="form-select" id="pengampu" name="pengampu">
                        <option value="">Semua</option>
                        <option value="ada" @selected($filter['pengampu'] === 'ada')>Sudah diisi</option>
                        <option value="belum" @selected($filter['pengampu'] === 'belum')>Belum diisi</option>
                    </select>
                </div>
                <div class="col-6 col-lg-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" title="Terapkan filter">
                        <i class="bx bx-filter-alt"></i><span class="d-lg-none ms-1">Terapkan</span>
                    </button>
                    @if (array_filter($filter))
                        <a href="{{ route('master-urusan.index') }}" class="btn btn-outline-secondary flex-fill" title="Reset filter">
                            <i class="bx bx-reset"></i><span class="d-lg-none ms-1">Reset</span>
                        </a>
                    @endif
                </div>
            </form>
            <div class="small text-muted mt-2">
                Menampilkan {{ $urusan->total() }} dari {{ $totalUrusan }} urusan
                @if (array_filter($filter))
                    sesuai filter
                @endif
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
                                @if ($item->kode_opd_pengampu)
                                    {{ $item->opd_pengampu }}
                                    <small class="d-block text-muted">{{ $item->kode_opd_pengampu }}</small>
                                @else
                                    <span class="badge bg-label-warning">Belum diisi</span>
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
                            <td colspan="7" class="text-center text-muted">
                                {{ array_filter($filter) ? 'Tidak ada urusan yang sesuai filter.' : 'Belum ada urusan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($urusan->hasPages())
            <div class="card-footer">
                {{ $urusan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
