@extends('layouts.app')

@section('title', 'Master OPD')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} alert-dismissible" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Daftar OPD</h5>
                <small class="text-muted">OPD lingkungan Provinsi yang dapat dibuatkan akun untuk memberi masukan pada urusan pemerintahan bidangnya.</small>
            </div>
            <a href="{{ route('opd.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah OPD
            </a>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('opd.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label small mb-1" for="q">Cari</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="search" class="form-control" id="q" name="q" value="{{ $filter['q'] }}"
                            placeholder="Kode, nama, atau singkatan OPD">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1" for="jenis">Jenis</label>
                    <select class="form-select" id="jenis" name="jenis">
                        <option value="">Semua</option>
                        @foreach (App\Models\Opd::JENIS_OPD as $jenis)
                            <option value="{{ $jenis }}" @selected($filter['jenis'] === $jenis)>{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1" for="rumpun">Rumpun Urusan</label>
                    <select class="form-select" id="rumpun" name="rumpun">
                        <option value="">Semua</option>
                        @foreach (App\Models\MasterUrusan::rumpunList() as $rumpun)
                            <option value="{{ $rumpun }}" @selected($filter['rumpun'] === $rumpun)>{{ $rumpun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-1">
                    <label class="form-label small mb-1" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua</option>
                        @foreach (App\Models\Opd::STATUS as $status)
                            <option value="{{ $status }}" @selected($filter['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1" for="urusan">Urusan Masukan</label>
                    <select class="form-select" id="urusan" name="urusan">
                        <option value="">Semua</option>
                        <option value="ada" @selected($filter['urusan'] === 'ada')>Sudah diatur</option>
                        <option value="belum" @selected($filter['urusan'] === 'belum')>Belum diatur</option>
                    </select>
                </div>
                <div class="col-12 col-lg-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" title="Terapkan filter">
                        <i class="bx bx-filter-alt"></i><span class="d-lg-none ms-1">Terapkan</span>
                    </button>
                    @if (array_filter($filter))
                        <a href="{{ route('opd.index') }}" class="btn btn-outline-secondary flex-fill" title="Reset filter">
                            <i class="bx bx-reset"></i><span class="d-lg-none ms-1">Reset</span>
                        </a>
                    @endif
                </div>
            </form>
            <div class="small text-muted mt-2">
                Menampilkan {{ $opd->total() }} dari {{ $totalOpd }} OPD
                @if (array_filter($filter))
                    sesuai filter
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode OPD</th>
                        <th>Nama OPD</th>
                        <th>Jenis / Rumpun</th>
                        <th>Urusan Masukan</th>
                        <th>Akun</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opd as $item)
                        <tr>
                            <td class="text-nowrap">{{ $item->kode_opd }}</td>
                            <td>
                                {{ $item->nama_opd }}
                                <small class="d-block text-muted">
                                    {{ $item->singkatan }}
                                    @if ($item->induk_opd)
                                        · induk {{ $item->induk?->singkatan ?? $item->induk_opd }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                {{ $item->jenis_opd }}
                                <small class="d-block text-muted">{{ $item->rumpun_urusan }}</small>
                            </td>
                            <td>
                                @forelse ($item->urusan as $urusan)
                                    <span class="badge bg-label-primary mb-1" title="{{ $urusan->nama_urusan }}">{{ $urusan->kode_urusan }} {{ $urusan->nama_urusan }}</span>
                                @empty
                                    <span class="badge bg-label-warning">Belum diatur</span>
                                @endforelse
                            </td>
                            <td>{{ $item->users_count }}</td>
                            <td>
                                <span class="badge {{ $item->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $item->status }}</span>
                                <small class="d-block text-muted">{{ $item->tanggal_update?->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('opd.edit', $item) }}" class="btn btn-sm btn-primary" title="Ubah">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                                <form action="{{ route('opd.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus OPD ini?')">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                {{ array_filter($filter) ? 'Tidak ada OPD yang sesuai filter.' : 'Belum ada OPD.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($opd->hasPages())
            <div class="card-footer">
                {{ $opd->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
