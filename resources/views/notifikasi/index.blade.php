@extends('layouts.app')

@section('title', 'Notifikasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Notifikasi</h4>
                        <p class="mb-0 text-muted">Semua notifikasi dan pemberitahuan sistem</p>
                    </div>
                    @if (count($notifikasi) > 0)
                        <form action="{{ route('notifikasi.destroy-all') }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua notifikasi?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bx bx-trash"></i> Hapus Semua
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-4 col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text">Total</p>
                                <div class="d-flex align-items-end mb-2">
                                    <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
                                </div>
                                <small class="text-muted">Notifikasi</small>
                            </div>
                            <div class="card-icon">
                                <span class="badge bg-label-primary rounded p-2">
                                    <i class="bx bx-bell bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text">Belum Dibaca</p>
                                <div class="d-flex align-items-end mb-2">
                                    <h4 class="mb-0 me-2">{{ $stats['belum_dibaca'] }}</h4>
                                </div>
                                <small class="text-muted">Notifikasi Baru</small>
                            </div>
                            <div class="card-icon">
                                <span class="badge bg-label-warning rounded p-2">
                                    <i class="bx bx-envelope bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text">Sudah Dibaca</p>
                                <div class="d-flex align-items-end mb-2">
                                    <h4 class="mb-0 me-2">{{ $stats['sudah_dibaca'] }}</h4>
                                </div>
                                <small class="text-muted">Notifikasi Lama</small>
                            </div>
                            <div class="card-icon">
                                <span class="badge bg-label-success rounded p-2">
                                    <i class="bx bx-check-circle bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('notifikasi.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua Status</option>
                                <option value="belum_dibaca" {{ request('status') == 'belum_dibaca' ? 'selected' : '' }}>
                                    Belum Dibaca</option>
                                <option value="sudah_dibaca" {{ request('status') == 'sudah_dibaca' ? 'selected' : '' }}>
                                    Sudah Dibaca</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select">
                                <option value="semua" {{ request('jenis') == 'semua' ? 'selected' : '' }}>Semua Jenis
                                </option>
                                <option value="info" {{ request('jenis') == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="success" {{ request('jenis') == 'success' ? 'selected' : '' }}>Sukses
                                </option>
                                <option value="warning" {{ request('jenis') == 'warning' ? 'selected' : '' }}>Peringatan
                                </option>
                                <option value="danger" {{ request('jenis') == 'danger' ? 'selected' : '' }}>Bahaya</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter"></i> Filter
                                </button>
                                <a href="{{ route('notifikasi.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-reset"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notifikasi List -->
        <div class="card">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($notifikasi as $item)
                        <div class="list-group-item {{ !$item['dibaca'] ? 'bg-light' : '' }}">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar">
                                        <span
                                            class="avatar-initial rounded-circle 
                                        @if ($item['jenis'] == 'success') bg-label-success
                                        @elseif($item['jenis'] == 'warning') bg-label-warning
                                        @elseif($item['jenis'] == 'danger') bg-label-danger
                                        @else bg-label-info @endif">
                                            <i class="bx {{ $item['icon'] }} bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0">{{ $item['judul'] }}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            @if (!$item['dibaca'])
                                                <span class="badge bg-primary badge-sm">Baru</span>
                                            @endif
                                            <form action="{{ route('notifikasi.destroy', $item['id']) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1"
                                                    style="line-height: 1;">
                                                    <i class="bx bx-trash" style="font-size: 14px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="mb-1 text-muted">{{ $item['pesan'] }}</p>
                                    <small class="text-muted">
                                        <i class="bx bx-time-five"></i>
                                        {{ $item['tanggal']->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-bell-off bx-lg text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if (count($notifikasi) > 0)
                <div class="card-footer text-center">
                    <form action="{{ route('notifikasi.mark-all-read') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-check-double"></i> Tandai Semua Sudah Dibaca
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
