{{-- Dashboard Kabupaten/Kota --}}
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
                            Dashboard {{ auth()->user()?->kabupatenKota?->getFullNameAttribute() ?? 'Kabupaten/Kota' }}
                            - Kelola Permohonan Anda
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
                                    <th>Tahun Anggaran</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Periode Fasilitasi</th>
                                    <th>Batas Permohonan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stats['jadwal_aktif'] as $jadwal)
                                    <tr>
                                        <td><strong>{{ $jadwal->tahun_anggaran }}</strong></td>
                                        <td>
                                            <span class="badge bg-label-primary">
                                                {{ strtoupper($jadwal->jenis_dokumen) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $jadwal->tanggal_mulai->format('d M Y') }}
                                            <small class="text-muted">s/d</small>
                                            {{ $jadwal->tanggal_selesai->format('d M Y') }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-label-{{ $jadwal->batas_permohonan < now() ? 'danger' : 'success' }}">
                                                {{ $jadwal->batas_permohonan->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('permohonan.create', ['jadwal_id' => $jadwal->id]) }}"
                                                class="btn btn-xs btn-primary">
                                                <i class='bx bx-plus'></i> Buat Permohonan
                                            </a>
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
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['my_permohonan'] }}</h3>
                    <small class="text-primary fw-semibold">
                        <i class='bx bx-bar-chart'></i> Semua permohonan
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
                        <span class="avatar-initial rounded bg-secondary">
                            <i class='bx bx-edit bx-sm'></i>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="fw-semibold d-block mb-1 text-muted">Draft</span>
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['draft_permohonan'] ?? 0 }}</h3>
                    <small class="text-secondary fw-semibold">
                        <i class='bx bx-pencil'></i> Belum disubmit
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
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['in_process_permohonan'] ?? 0 }}</h3>
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
                    <h3 class="card-title text-nowrap mb-2">{{ $stats['completed_permohonan'] ?? 0 }}</h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-check-double'></i> Terselesaikan
                    </small>
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
                                <th>Kabupaten/Kota</th>
                                <th>Jenis Dokumen</th>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['my_permohonan_list'] as $index => $permohonan)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $permohonan->kabupatenKota?->nama ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge bg-label-{{ $permohonan->jenis_dokumen === 'perda' ? 'primary' : 'info' }}">
                                            {{ strtoupper($permohonan->jenis_dokumen) }}
                                        </span>
                                    </td>
                                    <td>{{ $permohonan->tahun }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $permohonan->status_badge_class }}">
                                            {{ $permohonan->status_label }}
                                        </span>
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
