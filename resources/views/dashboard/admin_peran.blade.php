@extends('layouts.app')

@section('title', 'Dashboard Admin PERAN')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Dashboard Admin PERAN</h5>
                    <p class="text-muted">Koordinasi & Workflow Management</p>
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
                            <p class="card-title mb-1">Menunggu Verifikasi</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['pending_verifikasi'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
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
                            <p class="card-title mb-1">Sedang Dievaluasi</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['in_evaluation'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class='bx bx-file-find bx-sm'></i>
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
                            <p class="card-title mb-1">Menunggu Approval</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['pending_approval'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
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
                            <p class="card-title mb-1">Total Permohonan</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['total_permohonan'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class='bx bx-file bx-sm'></i>
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
                <h5 class="card-header">Aktivitas Terbaru</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                                <th>Target</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($stats['recent_permohonan'] as $index => $activity)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $activity->causer_name ?? 'System' }}</td>
                                <td>{{ $activity->description ?? 'N/A' }}</td>
                                <td>{{ $activity->subject_type ?? 'N/A' }}</td>
                                <td>{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->diffForHumans() : '-' }}</td>
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
                <h5 class="card-header">Statistik Ringkas</h5>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Permohonan Draft</span>
                        <span class="fw-medium">{{ \App\Models\Permohonan::where('status', 'draft')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Menunggu Verifikasi</span>
                        <span class="fw-medium">{{ \App\Models\Permohonan::where('status', 'submitted')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Sedang Dievaluasi</span>
                        <span class="fw-medium">{{ \App\Models\Permohonan::where('status', 'in_evaluation')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Selesai</span>
                        <span class="fw-medium">{{ \App\Models\Permohonan::where('status', 'completed')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection