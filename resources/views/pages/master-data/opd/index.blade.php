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
                                        · induk {{ $item->induk_opd }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                {{ $item->jenis_opd }}
                                <small class="d-block text-muted">{{ $item->rumpun_urusan }}</small>
                            </td>
                            <td>
                                @forelse ($item->urusan as $urusan)
                                    <span class="badge bg-label-primary mb-1">{{ $urusan->nama_urusan }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>{{ $item->users_count }}</td>
                            <td>
                                <span class="badge {{ $item->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $item->status }}</span>
                                <small class="d-block text-muted">{{ $item->tanggal_update?->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('opd.edit', $item) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                                <form action="{{ route('opd.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Yakin ingin menghapus OPD ini?')">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada OPD</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
