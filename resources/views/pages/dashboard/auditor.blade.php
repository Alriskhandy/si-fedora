@extends('layouts.app')

@section('title', 'Dashboard Auditor')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dashboard Auditor</h5>
                        <p class="text-muted">Monitoring & Audit Sistem</p>
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
                                <p class="card-title mb-1">Total Permohonan</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['total_permohonan'] }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class='bx bx-file bx-sm'></i>
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
                                <p class="card-title mb-1">Sedang Diproses</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['in_process'] }}</h4>
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

            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="me-1">
                                <p class="card-title mb-1">Selesai Bulan Ini</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['completed_this_month'] }}</h4>
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
                                <p class="card-title mb-1">Total Aktivitas</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['total_activities'] }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class='bx bx-history bx-sm'></i>
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
                    <h5 class="card-header">Riwayat Aktivitas Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aktivitas</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($stats['recent_activities'] as $index => $activity)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($activity->created_at)->format('d M Y H:i') }}</td>
                                        <td>{{ $activity->causer?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $activity->log_name ?? '-' }}</span>
                                        </td>
                                        <td>{{ $activity->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada aktivitas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class='bx bx-user'></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Auditor</small>
                                <strong>{{ Auth::user()->name }}</strong>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Total Users</small>
                            <strong>{{ $stats['total_users'] }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Kabupaten/Kota</small>
                            <strong>{{ $stats['total_kabkota'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <h5 class="card-header">Daftar Permohonan</h5>
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
                                        <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                        <td>
                                            <span
                                                class="badge bg-label-{{ $permohonan->status_akhir == 'selesai' ? 'success' : ($permohonan->status_akhir == 'proses' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($permohonan->status_akhir) }}
                                            </span>
                                        </td>
                                        <td>{{ $permohonan->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data permohonan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
