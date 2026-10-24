@extends('layouts.app')

@section('title', 'Dashboard Kabupaten/Kota')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- header row with greeting and notifications -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h3 class="card-title">DASHBOARD SI-FEDORA</h3>
                                <h5 class="card-title text-secondary">Selamat datang, {{ auth()->user()->name }}!</h5>
                                <p class="mb-4">Kelola permohonan Fasilitasi/Evaluasi Dokumen Perencanaan Anda.</p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <img src="../assets/img/illustrations/man-with-laptop-light.png" height="140"
                                    alt="Ilustrasi" data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                    data-app-light-img="illustrations/man-with-laptop-light.png" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3">Notifikasi</h6>
                        <div class="row g-0 text-center">
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="avatar mb-2">
                                        <i class="bx bx-bell bx-sm"></i>
                                    </span>
                                    <p class="mb-2 small text-muted">Total</p>
                                    <h2 class="mb-0 fw-bold">{{ $stats['notifications']['total'] }}</h2>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="avatar mb-2">
                                        <i class="bx bx-envelope-open bx-sm"></i>
                                    </span>
                                    <p class="mb-2 small text-muted">Belum</p>
                                    <h2 class="mb-0 fw-bold">{{ $stats['notifications']['unread'] }}</h2>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="avatar mb-2">
                                        <i class="bx bx-check-circle bx-sm"></i>
                                    </span>
                                    <p class="mb-2 small text-muted">Sudah</p>
                                    <h2 class="mb-0 fw-bold">{{ $stats['notifications']['read'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- second row: daftar permohonan dan jadwal -->
        <div class="row g-4 mb-4">
            <!-- Permohonan Saya -->
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <h5 class="m-0">Permohonan Saya</h5>
                        <a href="{{ route('permohonan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Jenis Dokumen</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($stats['permohonan_list'] ?? [] as $permohonan)
                                        <tr>
                                            <td>
                                                <small>{{ $permohonan['jenis_dokumen_nama'] ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $permohonan['tahun'] ?? '-' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($permohonan['status'] ?? null) {
                                                        'belum' => 'badge bg-label-secondary',
                                                        'proses' => 'badge bg-label-info',
                                                        'revisi' => 'badge bg-label-warning',
                                                        'selesai' => 'badge bg-label-success',
                                                        default => 'badge bg-label-secondary',
                                                    };
                                                    $statusLabel = match ($permohonan['status'] ?? null) {
                                                        'belum' => 'Belum',
                                                        'proses' => 'Proses',
                                                        'revisi' => 'Revisi',
                                                        'selesai' => 'Selesai',
                                                        default => 'Unknown',
                                                    };
                                                @endphp
                                                <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('permohonan.show', $permohonan['id']) }}"
                                                    class="btn btn-sm btn-icon btn-text-primary rounded-pill"
                                                    title="Lihat Detail">
                                                    <span class="ti-xs"><i class='bx bx-show'></i></span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
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

            <div class="col-lg-4 col-md-12">
                <div class="row row-cols-2 g-4 h-100">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center" style="min-height:150px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class='bx bx-file bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Total
                                        Permohonan</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['my_permohonan'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center" style="min-height:150px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-secondary">
                                            <i class='bx bx-edit bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Draft</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['draft_permohonan'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center" style="min-height:150px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class='bx bx-time bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Sedang
                                        Diproses</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['in_process_permohonan'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center"
                                style="min-height:150px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class='bx bx-check-circle bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Selesai
                                    </p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['completed_permohonan'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- third row: activity chart and summary cards -->
        <div class="row g-4 mb-4">
            <!-- Jadwal Fasilitasi Aktif -->
            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Jadwal Fasilitasi Aktif</h5>
                        <a href="{{ route('jadwal.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        @forelse ($stats['jadwal_aktif'] ?? [] as $jadwal)
                            <div class="mb-4 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-2">
                                            {{ $jadwal->jenisDokumen?->nama ?? strtoupper($jadwal->jenis_dokumen ?? '-') }}
                                        </h6>
                                        <small class="text-muted">
                                            Tahun Anggaran: {{ $jadwal->tahun_anggaran ?? '-' }}
                                        </small>
                                    </div>
                                    @php
                                        $isClosed = $jadwal->batas_permohonan < now();
                                    @endphp
                                    <span class="badge bg-label-{{ $isClosed ? 'danger' : 'success' }}">
                                        {{ $isClosed ? 'Ditutup' : 'Aktif' }}
                                    </span>
                                </div>
                                <p class="mb-2 small">
                                    <i class='bx bx-calendar'></i> <strong>Jadwal:</strong>
                                    {{ $jadwal->tanggal_mulai ? \Carbon\Carbon::parse($jadwal->tanggal_mulai)->format('d M Y') : '-' }}
                                    s/d
                                    {{ $jadwal->tanggal_selesai ? \Carbon\Carbon::parse($jadwal->tanggal_selesai)->format('d M Y') : '-' }}
                                </p>
                                <p class="mb-3 small">
                                    <i class='bx bx-time'></i> <strong>Batas Permohonan:</strong>
                                    {{ $jadwal->batas_permohonan ? \Carbon\Carbon::parse($jadwal->batas_permohonan)->format('d M Y') : '-' }}
                                </p>
                                @if (!$isClosed)
                                    <a href="{{ route('permohonan.create', ['jadwal_id' => $jadwal->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class='bx bx-plus'></i> Buat Permohonan
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <p class="small">Belum ada jadwal fasilitasi yang aktif</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <h5 class="m-0">Aktivitas Saya</h5>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Pilih periode" id="periodToggle">
                            <button type="button" class="btn btn-outline-primary active"
                                data-period="daily">Harian</button>
                            <button type="button" class="btn btn-outline-primary" data-period="weekly">Mingguan</button>
                            <button type="button" class="btn btn-outline-primary" data-period="monthly">Bulanan</button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="activityChart" style="min-height:250px;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Activity chart initialization
                const chartEl = document.querySelector('#activityChart');
                const activityData = @json($stats['activity_chart']);

                const config = {
                    chart: {
                        type: 'line',
                        height: 250,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        sparkline: {
                            enabled: false
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 4
                    },
                    xaxis: {
                        categories: activityData.daily.labels
                    },
                    yaxis: {
                        title: {
                            text: 'Jumlah'
                        }
                    },
                    series: [{
                        name: 'Aktivitas',
                        data: activityData.daily.data
                    }],
                    colors: ['#5A8DEE'],
                    grid: {
                        borderColor: '#ebe9f1'
                    }
                };

                const activityChart = new ApexCharts(chartEl, config);
                activityChart.render();

                // period toggle
                document.getElementById('periodToggle').addEventListener('click', function(e) {
                    if (e.target.tagName !== 'BUTTON') return;
                    const period = e.target.getAttribute('data-period');
                    // update button active state
                    Array.from(this.children).forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');

                    activityChart.updateOptions({
                        xaxis: {
                            categories: activityData[period].labels
                        }
                    });
                    activityChart.updateSeries([{
                        data: activityData[period].data
                    }]);
                });
            });
        </script>
    @endpush
@endsection
