{{-- Dashboard Superadmin --}}
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
                            Dashboard Superadmin - Kelola Sistem & Monitor Aktivitas
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
                            <i class='bx bx-user bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Total Users</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['total_users'] }}</h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-user-check'></i> Pengguna terdaftar
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
                            <i class='bx bx-file bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Total Permohonan</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['total_permohonan'] }}</h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-check'></i> Semua permohonan
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
                            <i class='bx bx-calendar bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Jadwal Aktif</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['active_jadwal'] }}</h3>
                    <small class="text-info fw-semibold">
                        <i class='bx bx-time'></i> Jadwal berjalan
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
                            <i class='bx bx-buildings bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Kab/Kota Terdaftar</span>
                    <h3 class="card-title text-nowrap mb-2">{{ \App\Models\KabupatenKota::count() }}</h3>
                    <small class="text-warning fw-semibold">
                        <i class='bx bx-map'></i> Wilayah aktif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <h5 class="card-header">Permohonan Terbaru</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kabupaten/Kota</th>
                            <th>Jenis Dokumen</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($stats['recent_permohonan'] as $index => $permohonan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                <td><span
                                        class="badge bg-label-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                                </td>
                                <td>{{ $permohonan->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <h5 class="card-header">Statistik Ringkas</h5>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Belum Dimulai</span>
                    <span
                        class="fw-medium">{{ \App\Models\Permohonan::where('status_akhir', 'belum')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Dalam Proses</span>
                    <span
                        class="fw-medium">{{ \App\Models\Permohonan::where('status_akhir', 'proses')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Perlu Revisi</span>
                    <span
                        class="fw-medium">{{ \App\Models\Permohonan::where('status_akhir', 'revisi')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Selesai</span>
                    <span
                        class="fw-medium">{{ \App\Models\Permohonan::where('status_akhir', 'selesai')->count() }}</span>
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
