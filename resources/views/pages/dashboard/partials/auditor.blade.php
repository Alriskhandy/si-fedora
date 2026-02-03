{{-- Dashboard Auditor --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="card-title text-white mb-1">
                            Selamat Datang, {{ auth()->user()->name }}! 👋
                        </h4>
                        <p class="text-white mb-0 opacity-75">
                            Dashboard Auditor - Monitoring & Pelaporan
                        </p>
                    </div>
                    <div class="d-none d-sm-block">
                        <div class="avatar avatar-xl">
                            <span class="avatar-initial rounded-circle bg-white text-primary">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card h-100 card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-primary">
                            <i class='bx bx-file bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Total Permohonan</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['total_permohonan'] }}</h3>
                    <small class="text-primary fw-semibold">
                        <i class='bx bx-bar-chart'></i> Semua data
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card h-100 card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-warning">
                            <i class='bx bx-time bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Sedang Diproses</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['in_progress'] }}</h3>
                    <small class="text-warning fw-semibold">
                        <i class='bx bx-hourglass'></i> Dalam proses
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
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
                    <span class="fw-semibold d-block mb-1 text-muted">Selesai</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['completed'] }}</h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-check-double'></i> Terselesaikan
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card h-100 card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-info">
                            <i class='bx bx-buildings bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Kab/Kota</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['total_kabkota'] }}</h3>
                    <small class="text-info fw-semibold">
                        <i class='bx bx-map'></i> Wilayah
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
                    <i class='bx bx-list-ul me-2'></i>Riwayat Permohonan Terbaru
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
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kabupaten/Kota</th>
                            <th>Jenis Dokumen</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($stats['recent_permohonan'] as $index => $permohonan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <i class='bx bx-buildings'></i>
                                            </span>
                                        </div>
                                        <span>{{ $permohonan->kabupatenKota->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ strtoupper($permohonan->jenis_dokumen) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $permohonan->status_badge_class }}">
                                        {{ $permohonan->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $permohonan->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('permohonan.show', $permohonan->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class='bx bx-show'></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class='bx bx-info-circle bx-lg text-muted mb-2'></i>
                                        <p class="text-muted mb-0">Belum ada data permohonan</p>
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
                    <i class='bx bx-pie-chart-alt-2 me-2'></i>Statistik Bulan Ini
                </h5>
            </div>
            <div class="card-body">
                @if (isset($stats['monthly_stats']) && $stats['monthly_stats']->count() > 0)
                    @foreach ($stats['monthly_stats'] as $stat)
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-capitalize">
                                @if ($stat->status_akhir == 'belum')
                                    Belum Dimulai
                                @elseif($stat->status_akhir == 'proses')
                                    Dalam Proses
                                @elseif($stat->status_akhir == 'revisi')
                                    Revisi
                                @elseif($stat->status_akhir == 'selesai')
                                    Selesai
                                @else
                                    {{ $stat->status_akhir }}
                                @endif
                            </span>
                            <span class="fw-medium">{{ $stat->count }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0">Belum ada data bulan ini</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-lightning-charge me-2'></i>Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('permohonan.index') }}" class="btn btn-primary">
                        <i class='bx bx-file-find me-2'></i>Lihat Semua Permohonan
                    </a>
                    <a href="#" class="btn btn-outline-secondary">
                        <i class='bx bx-download me-2'></i>Download Laporan
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class='bx bx-help-circle me-2'></i>Panduan Auditor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .card-hover {
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
