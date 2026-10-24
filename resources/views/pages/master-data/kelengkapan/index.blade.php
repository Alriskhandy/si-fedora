@extends('layouts.app')

@section('title', 'Master Kelengkapan Verifikasi')

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
                        <li class="breadcrumb-item active">Master Dokumen Kelengkapan</li>
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
                                <h5 class="card-title mb-1">Daftar Dokumen Kelengkapan</h5>
                                <p class="text-muted small mb-0">Kelola dokumen kelengkapan verifikasi</p>
                            </div>
                            <a href="{{ route('master-kelengkapan.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Tambah Data
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="card mb-4" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <form action="{{ route('master-kelengkapan.index') }}" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Cari Dokumen</label>
                                            <input type="text" class="form-control" name="search"
                                                value="{{ request('search') }}" placeholder="Cari nama dokumen...">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Jenis Dokumen</label>
                                            <select class="form-select" name="jenis_dokumen_id">
                                                <option value="">Semua Jenis</option>
                                                @foreach ($jenisDokumen as $jenis)
                                                    <option value="{{ $jenis->id }}"
                                                        {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                        {{ $jenis->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select class="form-select" name="wajib">
                                                <option value="">Semua Status</option>
                                                <option value="1" {{ request('wajib') === '1' ? 'selected' : '' }}>
                                                    Wajib</option>
                                                <option value="0" {{ request('wajib') === '0' ? 'selected' : '' }}>
                                                    Opsional</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="w-100">
                                                <button type="submit" class="btn btn-primary w-100 mb-1">
                                                    <i class="bx bx-search-alt me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('master-kelengkapan.index') }}"
                                                    class="btn btn-outline-secondary w-100">
                                                    <i class="bx bx-reset me-1"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th style="width: 35%;">Nama Dokumen</th>
                                        <th style="width: 120px;" class="text-center">Jenis Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 100px;" class="text-center">Status</th>
                                        <th style="width: 80px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelengkapan as $index => $item)
                                        <tr>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-label-dark rounded-pill">{{ $item->urutan ?: $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm flex-shrink-0 me-2">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            <i class="bx bx-file"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $item->nama_dokumen }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if ($item->jenisDokumen)
                                                    <span class="badge bg-info">{{ $item->jenisDokumen->nama }}</span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small
                                                    class="text-muted">{{ str()->limit($item->deskripsi, 60) ?: '-' }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if ($item->wajib)
                                                    <span class="badge bg-danger">
                                                        <i class="bx bx-error-circle"></i> Wajib
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bx bx-info-circle"></i> Opsional
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="{{ route('master-kelengkapan.edit', $item) }}">
                                                            <i class="bx bx-edit-alt me-2"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('master-kelengkapan.destroy', $item) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                                <i class="bx bx-trash me-2"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bx bx-folder-open bx-lg"></i>
                                                    <p class="mt-2 mb-0">Tidak ada data yang ditemukan</p>
                                                    @if (request()->hasAny(['search', 'jenis_dokumen_id', 'kategori', 'wajib']))
                                                        <a href="{{ route('master-kelengkapan.index') }}"
                                                            class="btn btn-sm btn-outline-secondary mt-2">
                                                            <i class="bx bx-reset me-1"></i> Reset Filter
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Notification helper
            function showNotification(type, message) {
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';

                const notification = $(`
                    <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed" 
                         role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        <i class='bx ${icon} me-2'></i>${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);

                $('body').append(notification);

                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        });
    </script>
@endpush
