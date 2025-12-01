@extends('layouts.app')

@section('title', 'Dashboard Verifikator')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Welcome Header with Gradient --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card bg-gradient-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                Selamat Datang, {{ Auth::user()->name }}! 👋
                            </h4>
                            <p class="text-white mb-0 opacity-75">
                                Dashboard Tim Verifikasi - Verifikasi Dokumen Permohonan
                            </p>
                        </div>
                        <div class="d-none d-sm-block">
                            <div class="avatar avatar-xl">
                                <span class="avatar-initial rounded-circle bg-white text-primary">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards with Enhanced Design --}}
    <div class="row g-4 mb-4">
        {{-- Tugas Saya Card --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-primary">
                                <i class='bx bx-task bx-sm'></i>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Lihat Detail</a>
                                <a class="dropdown-item" href="#">Refresh Data</a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="fw-semibold d-block mb-1 text-muted">Tugas Saya</span>
                        <h3 class="card-title text-nowrap mb-2">{{ $stats['my_verifikasi'] }}</h3>
                        <small class="text-success fw-semibold">
                            <i class='bx bx-up-arrow-alt'></i> Permohonan aktif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sudah Diverifikasi Card --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-success">
                                <i class='bx bx-check-circle bx-sm'></i>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Lihat Detail</a>
                                <a class="dropdown-item" href="#">Export Data</a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="fw-semibold d-block mb-1 text-muted">Sudah Diverifikasi</span>
                        <h3 class="card-title text-nowrap mb-2">{{ $stats['completed_verifikasi'] }}</h3>
                        <small class="text-success fw-semibold">
                            <i class='bx bx-check'></i> Selesai
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menunggu Card --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-warning">
                                <i class='bx bx-time-five bx-sm'></i>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Lihat Detail</a>
                                <a class="dropdown-item" href="#">Set Priority</a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="fw-semibold d-block mb-1 text-muted">Menunggu</span>
                        <h3 class="card-title text-nowrap mb-2">{{ $stats['pending_verifikasi'] }}</h3>
                        <small class="text-warning fw-semibold">
                            <i class='bx bx-time'></i> Pending
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="row">
        {{-- Tasks Table --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class='bx bx-list-ul me-2'></i>Tugas Verifikasi Saya
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class='bx bx-filter-alt'></i> Filter
                        </button>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class='bx bx-export'></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Jenis Dokumen</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($stats['my_tasks'] as $index => $permohonan)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <i class='bx bx-buildings'></i>
                                            </span>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong>
                                            <small class="text-muted">{{ $permohonan->created_at->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $permohonan->status_badge_class }}">
                                        {{ $permohonan->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('verifikasi.show', $permohonan->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class='bx bx-search-alt-2'></i> Verifikasi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class='bx bx-info-circle bx-lg text-muted mb-2'></i>
                                        <p class="text-muted mb-0">Tidak ada tugas verifikasi saat ini</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Info Sidebar --}}
        <div class="col-lg-4 mb-4">
            {{-- Progress Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class='bx bx-trending-up me-2'></i>Progress Verifikasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Target Mingguan</span>
                            <span class="fw-semibold">{{ $stats['completed_verifikasi'] }}/10</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ ($stats['completed_verifikasi']/10)*100 }}%">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Tugas Aktif</span>
                            <span class="fw-semibold">{{ $stats['my_verifikasi'] }}/20</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ ($stats['my_verifikasi']/20)*100 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class='bx bx-lightning-charge me-2'></i>Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-primary">
                            <i class='bx bx-file-find me-2'></i>Lihat Semua Tugas
                        </a>
                        <a href="#" class="btn btn-outline-secondary">
                            <i class='bx bx-history me-2'></i>Riwayat Verifikasi
                        </a>
                        <a href="#" class="btn btn-outline-info">
                            <i class='bx bx-help-circle me-2'></i>Panduan Verifikasi
                        </a>
                    </div>
                </div>
            </div>

            {{-- Activity Timeline --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class='bx bx-time-five me-2'></i>Aktivitas Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-primary"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Verifikasi Selesai</h6>
                                    <small class="text-muted">2 jam lalu</small>
                                </div>
                                <p class="mb-0 small">RPJMD Kota Ternate</p>
                            </div>
                        </li>
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Tugas Diterima</h6>
                                    <small class="text-muted">5 jam lalu</small>
                                </div>
                                <p class="mb-0 small">RKPD Kab. Halmahera</p>
                            </div>
                        </li>
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-info"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Review Dokumen</h6>
                                    <small class="text-muted">1 hari lalu</small>
                                </div>
                                <p class="mb-0 small">RPJMD Kota Tidore</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Custom Gradient for Header Card */
.bg-gradient-primary {
    background: linear-gradient(135deg, #254AF2FF 0%, #433DDAFF 100%);
}

/* Card Hover Effect */
.card-hover {
    transition: all 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
}

/* Timeline Styles */
.timeline {
    padding-left: 0;
    list-style: none;
}

.timeline-item {
    position: relative;
    padding-left: 2rem;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 0.4375rem;
    top: 1.25rem;
    width: 1px;
    height: calc(100% - 0.5rem);
    background-color: #ddd;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-point {
    position: absolute;
    left: 0;
    top: 0;
    width: 0.875rem;
    height: 0.875rem;
    border-radius: 50%;
    background-color: #fff;
    border: 2px solid;
}

.timeline-point-primary {
    border-color: #696cff;
}

.timeline-point-success {
    border-color: #71dd37;
}

.timeline-point-info {
    border-color: #03c3ec;
}

/* Table Enhancement */
.table-hover tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
}

/* Badge Enhancement */
.badge {
    font-weight: 500;
    padding: 0.375rem 0.75rem;
}
</style>
@endpush
@endsection


{{-- @extends('layouts.app')

@section('title', 'Dashboard Verifikator')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Dashboard Tim Verifikasi</h5>
                    <p class="text-muted">Verifikasi Dokumen Permohonan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="card-title mb-1">Tugas Saya</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['my_verifikasi'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class='bx bx-task bx-sm'></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="card-title mb-1">Sudah Diverifikasi</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['completed_verifikasi'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class='bx bx-check-circle bx-sm'></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="card-title mb-1">Menunggu</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['pending_verifikasi'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class='bx bx-time-five bx-sm'></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <h5 class="card-header">Tugas Verifikasi Saya</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Jenis Dokumen</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($stats['my_tasks'] as $index => $permohonan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('verifikasi.show', $permohonan->id) }}" class="btn btn-sm btn-primary">Verifikasi</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada tugas verifikasi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection --}}