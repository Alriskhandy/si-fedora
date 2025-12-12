@extends('layouts.app')

@section('title', 'Master Jenis Dokumen')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Master Data /</span> Master Jenis Dokumen
        </h4>

        <!-- Filter & Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('master-jenis-dokumen.index') }}">
                            <input type="text" class="form-control" name="search" placeholder="Cari nama dokumen..."
                                value="{{ request('search') }}">
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET" action="{{ route('master-jenis-dokumen.index') }}">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-5 text-end">
                        <a href="{{ route('master-jenis-dokumen.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Jenis Dokumen
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Daftar Jenis Dokumen</h5>
            </div>
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
@endsection
