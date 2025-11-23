@extends('layouts.app')

@section('title', 'Dashboard Kabupaten/Kota')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Dashboard {{ Auth::user()->kabupatenKota->nama ?? 'Kabupaten/Kota' }}</h5>
                    <p class="text-muted">Monitoring Permohonan Fasilitasi</p>
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
                                <h4 class="mb-0 me-2">{{ $stats['my_permohonan'] }}</h4>
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
                            <p class="card-title mb-1">Sudah Dikirim</p>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-2">{{ $stats['submitted_permohonan'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class='bx bx-send bx-sm'></i>
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
                                <h4 class="mb-0 me-2">{{ $stats['verified_permohonan'] }}</h4>
                            </div>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class='bx bx-check-circle bx-sm'></i>
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
                <h5 class="card-header">Permohonan Saya</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis Dokumen</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($stats['my_permohonan_list'] as $index => $permohonan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $permohonan->status_badge_class ?? 'secondary' }}">{{ $permohonan->status_label ?? $permohonan->status }}</span>
                                </td>
                                <td>{{ $permohonan->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('permohonan.show', $permohonan->id) }}" class="btn btn-sm btn-primary">Lihat</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada permohonan</td>
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