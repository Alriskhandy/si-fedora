{{-- Dashboard Verifikator --}}
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

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100 card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-primary">
                            <i class='bx bx-task bx-sm'></i>
                        </span>
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

    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100 card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-success">
                            <i class='bx bx-check-circle bx-sm'></i>
                        </span>
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

    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100 card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-warning">
                            <i class='bx bx-time-five bx-sm'></i>
                        </span>
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

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class='bx bx-list-ul me-2'></i>Tugas Verifikasi Saya
                </h5>
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
                                            <strong
                                                class="d-block">{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong>
                                            <small
                                                class="text-muted">{{ $permohonan->created_at->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ strtoupper($permohonan->jenis_dokumen) }}
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

    <div class="col-lg-4 mb-4">
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
                            style="width: {{ ($stats['completed_verifikasi'] / 10) * 100 }}%">
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
                            style="width: {{ ($stats['my_verifikasi'] / 20) * 100 }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
    </style>
@endpush
