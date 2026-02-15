@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dashboard Superadmin</h5>
                        <p class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></p>
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
                                <p class="card-title mb-1">Total Users</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['total_users'] }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class='bx bx-user bx-sm'></i>
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
                                <p class="card-title mb-1">Total Permohonan</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['total_permohonan'] }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
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
                                <p class="card-title mb-1">Jadwal Aktif</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['active_jadwal'] }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class='bx bx-calendar bx-sm'></i>
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
                                <p class="card-title mb-1">Kab/Kota Terdaftar</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ \App\Models\KabupatenKota::count() }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class='bx bx-buildings bx-sm'></i>
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
    </div>
@endsection
