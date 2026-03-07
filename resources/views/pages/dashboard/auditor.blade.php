@extends('layouts.app')

@section('title', 'Dashboard Auditor')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Row 1: Greeting and Notifications -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h3 class="card-title">DASHBOARD SI-FEDORA</h3>
                                <h5 class="card-title text-secondary">Selamat datang, {{ auth()->user()->name }}!</h5>
                                <p class="mb-4">Auditor - Monitoring dan Audit Sistem Fasilitasi Dokumen Perencanaan.</p>
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

        <!-- Row 2: Activity Chart and Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <h5 class="m-0">Aktivitas Sistem</h5>
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
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Total Permohonan</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['total_permohonan'] }}</h2>
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
                                            <i class='bx bx-time-five bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Sedang Diproses</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['in_process'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center" style="min-height:150px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class='bx bx-check-circle bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Selesai Bulan Ini</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['completed_this_month'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center" style="min-height:150px;">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-info">
                                            <i class='bx bx-history bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted text-truncate" style="font-size:0.85rem;">Total Aktivitas</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['total_activities'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Recent Activities and Recent Permohonan -->
        <div class="row g-4">
            <!-- Recent Activities -->
            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Riwayat Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>User</th>
                                        <th>Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['recent_activities'] as $activity)
                                        <tr>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($activity->created_at)->format('d M Y') }}</small><br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($activity->created_at)->format('H:i') }}</small>
                                            </td>
                                            <td>{{ $activity->causer?->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-label-primary mb-1">{{ $activity->log_name ?? '-' }}</span>
                                                <div class="small text-muted">{{ str()->limit($activity->description ?? '-', 50) }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                Tidak ada aktivitas
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Permohonan -->
            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Daftar Permohonan Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Kabupaten/Kota</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['recent_permohonan'] as $permohonan)
                                        <tr>
                                            <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                            <td>
                                                <div>{{ $permohonan->jenisDokumen->nama ?? '-' }}</div>
                                                <small class="text-muted">{{ $permohonan->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ match ($permohonan->status_akhir ?? 'draft') {
                                                        'belum' => 'secondary',
                                                        'proses' => 'primary',
                                                        'revisi' => 'warning',
                                                        'selesai' => 'success',
                                                        default => 'secondary',
                                                    } }}">
                                                    {{ match ($permohonan->status_akhir ?? 'draft') {
                                                        'belum' => 'Belum Diproses',
                                                        'proses' => 'Sedang Diproses',
                                                        'revisi' => 'Perlu Revisi',
                                                        'selesai' => 'Selesai',
                                                        default => 'Draft',
                                                    } }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                Tidak ada data permohonan
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
