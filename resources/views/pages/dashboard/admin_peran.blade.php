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
                    <h5 class="card-header">Aktivitas Terbaru (7 Hari Terakhir)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Pengguna</th>
                                    <th width="35%">Aktivitas</th>
                                    <th width="20%">Modul</th>
                                    <th width="20%">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($stats['recent_activities'] as $index => $activity)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><small>{{ $activity->causer_name ?? 'System' }}</small></td>
                                        <td><small>{{ $activity->description ?? '-' }}</small></td>
                                        <td>
                                            <small>
                                                @if($activity->subject_type)
                                                    @php
                                                        $type = str_replace('App\\Models\\', '', $activity->subject_type);
                                                        $type = preg_replace('/(?<!^)[A-Z]/', ' $0', $type);
                                                    @endphp
                                                    {{ $type }}
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                {{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->diffForHumans() : '-' }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Tidak ada aktivitas dalam 7 hari terakhir</td>
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
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <small class="text-muted d-block">Belum Dimulai</small>
                                <span class="fw-bold">{{ \App\Models\Permohonan::where('status_akhir', 'belum')->count() }}</span>
                            </div>
                            <span class="badge bg-label-secondary">Belum</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <small class="text-muted d-block">Sedang Diproses</small>
                                <span class="fw-bold">{{ \App\Models\Permohonan::where('status_akhir', 'proses')->count() }}</span>
                            </div>
                            <span class="badge bg-label-info">Proses</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <small class="text-muted d-block">Perlu Revisi</small>
                                <span class="fw-bold">{{ \App\Models\Permohonan::where('status_akhir', 'revisi')->count() }}</span>
                            </div>
                            <span class="badge bg-label-warning">Revisi</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Selesai</small>
                                <span class="fw-bold">{{ \App\Models\Permohonan::where('status_akhir', 'selesai')->count() }}</span>
                            </div>
                            <span class="badge bg-label-success">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Master Data, User & Tim Statistics --}}
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center">
                        <i class='bx bx-data bx-sm me-2'></i>
                        Master Data
                    </h5>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Kabupaten/Kota</small>
                                <span class="badge bg-label-primary rounded-pill">{{ $stats['master_data']['kabupaten_kota'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Jenis Dokumen</small>
                                <span class="badge bg-label-info rounded-pill">{{ $stats['master_data']['jenis_dokumen'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Tahapan</small>
                                <span class="badge bg-label-success rounded-pill">{{ $stats['master_data']['tahapan'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Sistematika (BAB)</small>
                                <span class="badge bg-label-warning rounded-pill">{{ $stats['master_data']['bab'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Urusan Pemerintahan</small>
                                <span class="badge bg-label-secondary rounded-pill">{{ $stats['master_data']['urusan'] }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Kelengkapan Dokumen</small>
                                <span class="badge bg-label-dark rounded-pill">{{ $stats['master_data']['kelengkapan'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center">
                        <i class='bx bx-user bx-sm me-2'></i>
                        Akun Pengguna
                    </h5>
                    <div class="card-body">
                        <div class="mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class='bx bx-group text-primary me-1'></i>
                                    <strong>Total Pengguna</strong>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $stats['users']['total'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Superadmin</small>
                                <span class="text-muted">{{ $stats['users']['superadmin'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Kepala Badan</small>
                                <span class="text-muted">{{ $stats['users']['kaban'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Admin PERAN</small>
                                <span class="text-muted">{{ $stats['users']['admin_peran'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Verifikator</small>
                                <span class="text-muted">{{ $stats['users']['verifikator'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Fasilitator</small>
                                <span class="text-muted">{{ $stats['users']['fasilitator'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Pemohon (Kab/Kota)</small>
                                <span class="text-muted">{{ $stats['users']['pemohon'] }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Auditor</small>
                                <span class="text-muted">{{ $stats['users']['auditor'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center">
                        <i class='bx bx-group bx-sm me-2'></i>
                        Tim FEDORA
                    </h5>
                    <div class="card-body">
                        <div class="mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class='bx bx-shield-alt-2 text-success me-1'></i>
                                    <strong>Tim Aktif</strong>
                                </div>
                                <span class="badge bg-success rounded-pill">{{ $stats['tim_assignments']['active'] }}</span>
                            </div>
                        </div>
                        <div class="mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Total Tim (Semua)</small>
                                </div>
                                <span class="text-muted">{{ $stats['tim_assignments']['total'] }}</span>
                            </div>
                        </div>
                        <div class="mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class='bx bx-user-check text-info me-1'></i>
                                    <strong>Total Anggota</strong>
                                </div>
                                <span class="badge bg-info rounded-pill">{{ $stats['tim_assignments']['total_members'] }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Verifikator</small>
                                <span class="text-muted">{{ $stats['tim_assignments']['verifikator'] }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Fasilitator</small>
                                <span class="text-muted">{{ $stats['tim_assignments']['fasilitator'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <h5 class="card-header">Permohonan Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Status</th>
                                    <th>Tanggal Diterima</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_permohonan'] as $index => $permohonan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                        <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                        <td>
                                            @if($permohonan->status_akhir == 'belum')
                                                <span class="badge bg-secondary">Belum Dimulai</span>
                                            @elseif($permohonan->status_akhir == 'proses')
                                                <span class="badge bg-info">Sedang Diproses</span>
                                            @elseif($permohonan->status_akhir == 'revisi')
                                                <span class="badge bg-warning">Perlu Revisi</span>
                                            @elseif($permohonan->status_akhir == 'selesai')
                                                <span class="badge bg-success">Selesai</span>
                                            @else
                                                <span class="badge bg-dark">{{ $permohonan->status_akhir }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $permohonan->created_at ? $permohonan->created_at->format('d/m/Y H:i') : '-' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Belum ada permohonan</td>
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
