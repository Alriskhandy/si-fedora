@extends('layouts.app')

@section('title', 'Dashboard Kaban')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Dashboard Kepala Badan</h5>
                    <p class="text-muted">Monitoring & Approval</p>
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
                            <p class="card-title mb-1">Menunggu Approval</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['pending_approval'] }}</h4>
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
                            <p class="card-title mb-1">Selesai Bulan Ini</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['completed_this_month'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class='bx bx-calendar-check bx-sm'></i>
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
                <h5 class="card-header">Draft Evaluasi Menunggu Approval</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Jenis Dokumen</th>
                                <th>Disubmit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($stats['recent_permohonan'] as $index => $permohonan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                <td>{{ $permohonan->updated_at->format('d M Y') ?? '-' }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada draft menunggu approval</td>
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