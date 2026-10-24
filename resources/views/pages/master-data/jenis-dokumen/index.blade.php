@extends('layouts.app')

@section('title', 'Master Jenis Dokumen')

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
                        <li class="breadcrumb-item active">Master Jenis Dokumen</li>
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

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1">Daftar Jenis Dokumen</h5>
                                <p class="text-muted small mb-0">Kelola jenis dokumen dalam sistem</p>
                            </div>
                            <a href="{{ route('master-jenis-dokumen.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Tambah Data
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="card mb-4" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <form action="{{ route('master-jenis-dokumen.index') }}" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Cari Dokumen</label>
                                            <input type="text" class="form-control" name="search"
                                                value="{{ request('search') }}" placeholder="Cari nama dokumen...">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="">Semua Status</option>
                                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bx bx-search me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('master-jenis-dokumen.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-reset me-1"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Nama Jenis Dokumen</th>
                            <th>Status</th>
                            <th>Jumlah Bab</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jenisDokumens as $index => $jenisDokumen)
                            <tr>
                                <td>{{ $jenisDokumens->firstItem() + $index }}</td>
                                <td><strong>{{ $jenisDokumen->nama }}</strong></td>
                                <td>
                                    <form action="{{ route('master-jenis-dokumen.toggle-status', $jenisDokumen) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                {{ $jenisDokumen->status ? 'checked' : '' }} onchange="this.form.submit()"
                                                title="{{ $jenisDokumen->status ? 'Aktif - Klik untuk nonaktifkan' : 'Nonaktif - Klik untuk aktifkan' }}"
                                                style="cursor: pointer;">
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    @if ($jenisDokumen->babs_count > 0)
                                        <span class="badge bg-label-info">{{ $jenisDokumen->babs_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('master-jenis-dokumen.edit', $jenisDokumen) }}"
                                        class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    <form action="{{ route('master-jenis-dokumen.destroy', $jenisDokumen) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus jenis dokumen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bx bx-file" style="font-size: 48px;"></i>
                                    <p class="mb-0 mt-2">Tidak ada data jenis dokumen</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $jenisDokumens->links() }}
            </div>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
