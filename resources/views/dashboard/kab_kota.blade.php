@extends('layouts.app')

@section('title', 'Dashboard Kabupaten/Kota')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dashboard
                            {{ auth()->user()?->kabupatenKota?->getFullNameAttribute() ?? 'Kabupaten/Kota' }}</h5>
                        <p class="text-muted">Sistem Informasi Fasilitasi Evaluasi Dokumen Perencanaan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Aktif -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Jadwal Fasilitasi Aktif</h5>
                        <a href="{{ route('pemohon.jadwal.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        @if (isset($stats['jadwal_aktif']) && $stats['jadwal_aktif']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Jenis Dokumen</th>
                                            <th>Tahun Anggaran</th>
                                            <th>Batas Permohonan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stats['jadwal_aktif'] as $jadwal)
                                            <tr>
                                                <td>{{ $jadwal->jenisDokumen->nama ?? '-' }}</td>
                                                <td>{{ $jadwal->tahunAnggaran->tahun ?? '-' }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $jadwal->batas_permohonan > now()->addDays(7) ? 'success' : 'warning' }}">
                                                        {{ $jadwal->batas_permohonan->format('d M Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('permohonan.create', ['jadwal_id' => $jadwal->id]) }}"
                                                        class="btn btn-xs btn-primary">Buat Permohonan</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">Belum ada jadwal fasilitasi yang aktif</p>
                        @endif
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
                                <p class="card-title mb-1">Draft</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['draft_permohonan'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class='bx bx-edit bx-sm'></i>
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
                                    <h4 class="mb-0 me-2">{{ $stats['in_process_permohonan'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class='bx bx-time bx-sm'></i>
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
                                <p class="card-title mb-1">Selesai</p>
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-2">{{ $stats['completed_permohonan'] ?? 0 }}</h4>
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
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Permohonan Saya</h5>
                        <a href="{{ route('permohonan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Nama Dokumen</th>
                                        <th>Status</th>
                                        <th width="20%">Progress</th>
                                        <th>Tanggal</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['my_permohonan_list'] as $index => $permohonan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $permohonan->jenisDokumen->nama ?? '-' }}</strong>
                                            </td>
                                            <td>{{ str()->limit($permohonan->nama_dokumen, 30) }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ $permohonan->status_badge_class ?? 'secondary' }}">
                                                    {{ $permohonan->status_label ?? $permohonan->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $currentStep = $permohonan->getCurrentStepIndex();
                                                    $totalSteps = 7;
                                                    $percentage = ($currentStep / ($totalSteps - 1)) * 100;
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress w-100 me-2" style="height: 6px;">
                                                        <div class="progress-bar bg-{{ $permohonan->status === 'rejected' ? 'danger' : ($percentage >= 100 ? 'success' : 'primary') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $percentage }}%"
                                                             aria-valuenow="{{ $percentage }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <small class="text-nowrap text-muted">{{ round($percentage) }}%</small>
                                                </div>
                                                <small class="text-muted">{{ $permohonan->status_label }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $permohonan->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('permohonan.show', $permohonan->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class='bx bx-show'></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class='bx bx-folder-open bx-lg mb-2 d-block'></i>
                                                Belum ada permohonan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
