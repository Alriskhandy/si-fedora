@extends('layouts.app')

@section('title', 'Kabupaten/Kota')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Master Data
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kabupaten/Kota</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1">Daftar Kabupaten/Kota</h5>
                                <p class="text-muted small mb-0">Kelola data kabupaten dan kota</p>
                            </div>
                            <a href="{{ route('kabupaten-kota.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Tambah Data
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="card mb-4" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <form action="{{ route('kabupaten-kota.index') }}" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-9">
                                            <label class="form-label small fw-semibold">Cari Kabupaten/Kota</label>
                                            <input type="text" class="form-control" name="search"
                                                value="{{ request('search') }}" placeholder="Cari nama atau kode...">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bx bx-search me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('kabupaten-kota.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-reset me-1"></i> Reset
                                            </a>
                                        </div>
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
                                                <span
                                                    class="badge bg-label-{{ $item->jenis == 'kabupaten' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($item->jenis) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($item->is_active)
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
                                                        <a class="dropdown-item"
                                                            href="{{ route('kabupaten-kota.show', $item) }}">
                                                            <i class="bx bx-show me-1"></i> Detail
                                                        </a>
                                                        <a class="dropdown-item"
                                                            href="{{ route('kabupaten-kota.edit', $item) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <form action="{{ route('kabupaten-kota.destroy', $item) }}"
                                                            method="POST" class="d-inline">
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
                                            <td colspan="6" class="text-center text-muted">Tidak ada data</td>
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
