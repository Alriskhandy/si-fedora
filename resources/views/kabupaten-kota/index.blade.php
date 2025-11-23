@extends('layouts.app')

@section('title', 'Kabupaten/Kota')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Kabupaten/Kota</h5>
                    <a href="{{ route('kabupaten-kota.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Kabupaten/Kota
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('kabupaten-kota.index') }}">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari kabupaten/kota..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bx bx-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Jenis</th>
                                    <th>Kepala Daerah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kabupatenKota as $index => $item)
                                <tr>
                                    <td>{{ $index + $kabupatenKota->firstItem() }}</td>
                                    <td>{{ $item->kode }}</td>
                                    <td>{{ $item->getFullNameAttribute() }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $item->jenis == 'kabupaten' ? 'primary' : 'info' }}">
                                            {{ ucfirst($item->jenis) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->kepala_daerah ?? '-' }}</td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-label-success">Aktif</span>
                                        @else
                                            <span class="badge bg-label-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('kabupaten-kota.show', $item) }}">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="{{ route('kabupaten-kota.edit', $item) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('kabupaten-kota.destroy', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" 
                                                            onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
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

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $kabupatenKota->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection