@extends('layouts.app')

@section('title', 'Dashboard Kaban')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- header row with greeting and notifications -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h3 class="card-title">Dashboard Kepala Badan</h3>
                                <h5 class="card-title text-secondary">Selamat datang, {{ auth()->user()->name }}!</h5>
                                <p class="mb-4">Monitoring & Approval terhadap hasil evaluasi dari tim fasilitasi.</p>
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

        <!-- second row: activity chart and hasil fasilitasi approval -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <h5 class="m-0">Aktivitas Pengguna</h5>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Pilih periode" id="periodToggle">
                            <button type="button" class="btn btn-outline-primary active"
                                data-period="daily">Harian</button>
                            <button type="button" class="btn btn-outline-primary" data-period="weekly">Mingguan</button>
                            <button type="button" class="btn btn-outline-primary" data-period="monthly">Bulanan</button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="activityChart" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Hasil Fasilitasi Menunggu Approval</h5>
                        <span class="badge bg-label-primary">{{ $stats['pending_approval'] }}</span>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @forelse ($stats['hasil_fasilitasi_approval'] ?? [] as $hasil)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('approval.show', $hasil->permohonan_id) }}" class="text-dark">
                                                {{ $hasil->permohonan?->kabupatenKota?->nama ?? '-' }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            {{ $hasil->permohonan?->jenisDokumen?->nama ?? '-' }} - 
                                            Tahun {{ $hasil->permohonan?->tahun ?? '-' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-label-warning">Menunggu</span>
                                </div>
                                <p class="mb-2 small">
                                    <i class='bx bx-time'></i>
                                    Diajukan: {{ $hasil->tanggal_diajukan_kaban ? \Carbon\Carbon::parse($hasil->tanggal_diajukan_kaban)->format('d M Y H:i') : '-' }}
                                </p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('approval.show', $hasil->permohonan_id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class='bx bx-show'></i> Review
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class='bx bx-check-circle bx-lg mb-2'></i>
                                <p class="mb-0">Tidak ada hasil fasilitasi yang menunggu approval</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- third row: daftar permohonan dan penetapan jadwal -->
        <div class="row g-4">
            <!-- Daftar Permohonan -->
            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0 flex-wrap gap-2">
                        <h5 class="m-0">Daftar Permohonan</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <select class="form-select form-select-sm" id="filterTahun" style="width: auto;">
                                <option value="">Semua Tahun</option>
                                @for ($year = date('Y'); $year >= 2020; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                            <select class="form-select form-select-sm" id="filterJenisDokumen" style="width: auto;">
                                <option value="">Semua Jenis Dokumen</option>
                                @foreach ($stats['jenis_dokumen_list'] ?? [] as $id => $nama)
                                    <option value="{{ $id }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                            <select class="form-select form-select-sm" id="filterKabKota" style="width: auto;">
                                <option value="">Semua Kab/Kota</option>
                                @foreach ($stats['kabupaten_kota_list'] ?? [] as $id => $nama)
                                    <option value="{{ $id }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Kab/Kota</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="permohonanTableBody">
                                    @forelse ($stats['permohonan_list'] ?? [] as $permohonan)
                                        <tr class="permohonan-row" data-tahun="{{ $permohonan['tahun'] ?? '' }}"
                                            data-jenis="{{ $permohonan['jenis_dokumen_id'] ?? '' }}"
                                            data-kabkota="{{ $permohonan['kabupaten_kota_id'] ?? '' }}">
                                            <td>
                                                <small>{{ $permohonan['kabupaten_kota_nama'] ?? '-' }}</small>
                                            </td>
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
                                            <td colspan="5" class="text-center text-muted py-4">
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

            <!-- Penetapan Jadwal -->
            <div class="col-lg-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Penetapan Jadwal</h5>
                        <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        @forelse ($stats['penetapan_jadwal'] ?? [] as $penetapan)
                            <div class="mb-4 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-2">
                                            {{ $penetapan->permohonan?->kabupatenKota?->nama ?? '-' }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $penetapan->permohonan?->jenisDokumen?->nama ?? '-' }} 
                                            - Tahun {{ $penetapan->permohonan?->tahun ?? '-' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-label-success">
                                        Ditetapkan
                                    </span>
                                </div>
                                <p class="mb-2 small">
                                    <i class='bx bx-calendar'></i> <strong>Jadwal Fasilitasi:</strong>
                                    {{ $penetapan->tanggal_mulai ? \Carbon\Carbon::parse($penetapan->tanggal_mulai)->format('d M Y') : '-' }}
                                    s/d
                                    {{ $penetapan->tanggal_selesai ? \Carbon\Carbon::parse($penetapan->tanggal_selesai)->format('d M Y') : '-' }}
                                </p>
                                @if($penetapan->lokasi)
                                <p class="mb-2 small">
                                    <i class='bx bx-map'></i> <strong>Lokasi:</strong> {{ $penetapan->lokasi }}
                                </p>
                                @endif
                                <p class="mb-0 small">
                                    <i class='bx bx-time'></i> <strong>Ditetapkan:</strong>
                                    {{ $penetapan->tanggal_penetapan ? \Carbon\Carbon::parse($penetapan->tanggal_penetapan)->format('d M Y H:i') : '-' }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <p class="small">Tidak ada penetapan jadwal</p>
                            </div>
                        @endforelse
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

                // Filter permohonan
                const filterTahun = document.getElementById('filterTahun');
                const filterJenisDokumen = document.getElementById('filterJenisDokumen');
                const filterKabKota = document.getElementById('filterKabKota');
                const permohonanRows = document.querySelectorAll('.permohonan-row');

                function applyFilters() {
                    const tahun = filterTahun.value;
                    const jenis = filterJenisDokumen.value;
                    const kabkota = filterKabKota.value;

                    permohonanRows.forEach(row => {
                        let show = true;

                        if (tahun && row.getAttribute('data-tahun') !== tahun) {
                            show = false;
                        }
                        if (jenis && row.getAttribute('data-jenis') !== jenis) {
                            show = false;
                        }
                        if (kabkota && row.getAttribute('data-kabkota') !== kabkota) {
                            show = false;
                        }

                        row.style.display = show ? '' : 'none';
                    });

                    // Show "no data" message if all rows are hidden
                    const visibleRows = document.querySelectorAll('.permohonan-row:not([style*="display: none"])');
                    const noDataRow = document.querySelector('tr:has(td[colspan="5"])');
                    if (noDataRow) {
                        noDataRow.style.display = visibleRows.length === 0 ? '' : 'none';
                    }
                }

                filterTahun.addEventListener('change', applyFilters);
                filterJenisDokumen.addEventListener('change', applyFilters);
                filterKabKota.addEventListener('change', applyFilters);
            });
        </script>
    @endpush
@endsection
