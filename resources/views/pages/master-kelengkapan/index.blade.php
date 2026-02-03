@extends('layouts.app')

@section('title', 'Master Kelengkapan Verifikasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible position-fixed top-0 end-0 m-3" role="alert"
                style="z-index: 9999; min-width: 300px;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Info Cards -->
            <div class="col-md-6 col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                <i class="bx bx-file-blank bx-sm"></i>
                            </div>
                            <div class="card-info">
                                <h5 class="mb-0">{{ $kelengkapan->count() }}</h5>
                                <small>Total Kelengkapan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded-pill bg-label-success me-3 p-2">
                                <i class="bx bx-check-circle bx-sm"></i>
                            </div>
                            <div class="card-info">
                                <h5 class="mb-0">{{ $kelengkapan->where('wajib', true)->count() }}</h5>
                                <small>Dokumen Wajib</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Master Kelengkapan Verifikasi</h5>
                        <a href="{{ route('master-kelengkapan.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Kelengkapan
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <form method="GET" action="{{ route('master-kelengkapan.index') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Dokumen</label>
                                    <select name="jenis_dokumen_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Jenis Dokumen</option>
                                        @foreach ($jenisDokumen as $jd)
                                            <option value="{{ $jd->id }}"
                                                {{ request('jenis_dokumen_id') == $jd->id ? 'selected' : '' }}>
                                                {{ $jd->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    @if (request()->hasAny(['jenis_dokumen_id']))
                                        <a href="{{ route('master-kelengkapan.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-reset me-1"></i> Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Nama Dokumen</th>
                                        <th width="80" class="text-center">Urutan</th>
                                        <th width="80" class="text-center">Status</th>
                                        <th width="100" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelengkapan as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-label-primary">{{ $item->jenisDokumen->nama ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $item->nama_dokumen }}</strong>
                                                @if ($item->deskripsi)
                                                    <br><small
                                                        class="text-muted">{{ \Illuminate\Support\Str::limit($item->deskripsi, 80) }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-secondary">{{ $item->urutan ?? '-' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($item->wajib)
                                                    <span class="badge bg-label-danger">Wajib</span>
                                                @else
                                                    <span class="badge bg-label-warning">Opsional</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('master-kelengkapan.edit', $item) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <form action="{{ route('master-kelengkapan.destroy', $item) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Yakin ingin menghapus kelengkapan ini?')">
                                                                <i class="bx bx-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bx bx-folder-open" style="font-size: 48px;"></i>
                                                <p class="mt-2">Tidak ada data kelengkapan verifikasi</p>
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
