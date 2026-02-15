@extends('layouts.app')

@section('title', 'Detail Permohonan')

@push('styles')
    <style>
        /* Disabled Tab Styling */
        .nav-tabs .nav-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Timeline Styling */
        .timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-icon {
            font-size: 1.2rem;
        }

        /* Sticky Progress */
        .sticky-top {
            z-index: 1020;
        }

        /* Badge adjustments */
        .nav-tabs .badge {
            font-size: 0.7rem;
            padding: 0.2em 0.4em;
        }

        /* Progress bar in card */
        .progress {
            background-color: #e9ecef;
        }

        /* Responsive tabs */
        @media (max-width: 768px) {
            .nav-tabs .nav-link {
                font-size: 0.85rem;
                padding: 0.5rem 0.3rem;
            }

            .nav-tabs .nav-link i {
                font-size: 1rem;
            }
        }

        /* Step Status Styles */
        /* Pending - Abu-abu, tidak bisa akses */
        .step-pending .step-circle {
            background: linear-gradient(135deg, #E0E0E0 0%, #EEEEEE 100%) !important;
            border: 2px solid #E0E0E0 !important;
            color: #BDBDBD !important;
            box-shadow: 0 4px 10px rgba(224, 224, 224, 0.3) !important;
        }

        .step-pending .step-name {
            color: #9E9E9E !important;
            font-weight: 400 !important;
        }

        .step-pending {
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.6;
        }

        /* On Proses - Kuning, bisa akses */
        .step-active .step-circle {
            background: linear-gradient(135deg, #FFA726 0%, #FFB74D 100%) !important;
            border: 2px solid #FF9800 !important;
            color: #FFFFFF !important;
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4), 0 0 0 4px rgba(255, 167, 38, 0.2) !important;
        }

        .step-active .step-name {
            color: #F57C00 !important;
            font-weight: 600 !important;
        }

        .step-active:hover .step-circle {
            transform: scale(1.1);
            box-shadow: 0 10px 25px rgba(255, 152, 0, 0.5), 0 0 0 4px rgba(255, 167, 38, 0.3) !important;
        }

        .step-active:hover .step-name {
            color: #EF6C00 !important;
        }

        /* Selesai - Biru, bisa akses */
        .step-completed .step-circle {
            background: linear-gradient(135deg, #2196F3 0%, #42A5F5 50%, #64B5F6 100%) !important;
            border: 2px solid #1976D2 !important;
            color: #FFFFFF !important;
            box-shadow: 0 8px 20px rgba(33, 150, 243, 0.35), 0 0 0 4px rgba(100, 181, 246, 0.2) !important;
        }

        .step-completed .step-name {
            color: #1565C0 !important;
            font-weight: 600 !important;
        }

        .step-completed:hover .step-circle {
            transform: scale(1.1);
            box-shadow: 0 10px 25px rgba(33, 150, 243, 0.4) !important;
        }

        .step-completed:hover .step-name {
            color: #0D47A1 !important;
        }

        .step-item {
            transition: all 0.3s ease;
        }

        /* Connector Line Colors */
        .connector-completed {
            background: linear-gradient(90deg, #42A5F5 0%, #64B5F6 100%) !important;
            box-shadow: 0 3px 8px rgba(66, 165, 245, 0.25) !important;
        }

        .connector-active {
            background: linear-gradient(90deg, #42A5F5 0%, #FFB74D 50%, #E0E0E0 100%) !important;
        }

        .connector-pending {
            background: #E0E0E0 !important;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Detail Permohonan
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Progress Tracker as Navigation -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="progress-tracker-navigation">
                    @php
                        $steps = $permohonan->getProgressSteps();
                        $currentIndex = $permohonan->getCurrentStepIndex();

                        // Map tahapan ke route
                        $tahapanRoutes = [
                            'Permohonan' => 'permohonan.tahapan.permohonan',
                            'Verifikasi' => 'permohonan.tahapan.verifikasi',
                            'Penetapan Jadwal' => 'permohonan.tahapan.jadwal',
                            'Pelaksanaan' => 'permohonan.tahapan.pelaksanaan',
                            'Hasil Fasilitasi / Evaluasi' => 'permohonan.tahapan.hasil',
                            'Tindak Lanjut Hasil' => 'permohonan.tahapan.tindak-lanjut',
                            'Penetapan PERDA / PERKADA' => 'permohonan.tahapan.penetapan',
                        ];
                    @endphp

                    <!-- Desktop View - Horizontal -->
                    <div class="d-none d-lg-block">
                        <div class="d-flex justify-content-between align-items-start position-relative"
                            style="width: 100%; margin: 0 auto;">
                            @foreach ($steps as $index => $step)
                                @php
                                    $routeName = $tahapanRoutes[$step['name']] ?? null;
                                    // Gunakan status dari model: pending, active, completed
                                    $stepStatus = $step['status']; // pending | active | completed
                                    $isAccessible = in_array($stepStatus, ['active', 'completed']);
                                    $routeUrl = $routeName && $isAccessible ? route($routeName, $permohonan) : '#';

                                    // CSS class berdasarkan status
                                    $stepClass = 'step-' . $stepStatus; // step-pending | step-active | step-completed
                                @endphp

                                <div class="position-relative text-decoration-none step-item {{ $stepClass }}"
                                    style="flex: 1; display: flex; flex-direction: column; align-items: center;">

                                    <!-- Connector Line -->
                                    @if ($index < count($steps) - 1)
                                        @php
                                            $nextStep = $steps[$index + 1] ?? null;
                                            // Line biru jika kedua step completed
                                            // Line gradient jika current completed dan next active
                                            // Line abu jika pending
                                            if (
                                                $step['status'] === 'completed' &&
                                                $nextStep &&
                                                $nextStep['status'] === 'completed'
                                            ) {
                                                $connectorClass = 'connector-completed';
                                            } elseif (
                                                $step['status'] === 'completed' &&
                                                $nextStep &&
                                                $nextStep['status'] === 'active'
                                            ) {
                                                $connectorClass = 'connector-active';
                                            } else {
                                                $connectorClass = 'connector-pending';
                                            }
                                        @endphp
                                        <div class="position-absolute {{ $connectorClass }}"
                                            style="left: 50%; top: 30px; width: 100%; height: 4px; z-index: 0; border-radius: 2px;">
                                        </div>
                                    @endif

                                    <!-- Circle Icon -->
                                    <a href="{{ $routeUrl }}" class="d-block text-decoration-none position-relative"
                                        style="z-index: 1; cursor: {{ $isAccessible ? 'pointer' : 'not-allowed' }}; pointer-events: {{ $isAccessible ? 'auto' : 'none' }};">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3 step-circle"
                                            style="width: 60px; height: 60px; font-size: 28px; transition: all 0.3s ease;">
                                            @if ($step['status'] === 'completed')
                                                <i class='bx bx-check' style="font-weight: bold;"></i>
                                            @elseif($step['status'] === 'active')
                                                <i class='bx bx-time-five'></i>
                                            @else
                                                <i class='bx bx-lock-alt'></i>
                                            @endif
                                        </div>
                                    </a>

                                    <!-- Step Label -->
                                    <div class="mt-2 text-center px-2" style="min-height: 60px;">
                                        <h6 class="mb-1 step-name" style="font-size: 0.85rem; line-height: 1.3;">
                                            {{ $step['name'] }}
                                        </h6>

                                        @if ($step['status'] === 'active')
                                            <div class="mt-1">
                                                <span class="badge bg-warning" style="font-size: 0.7rem;">Sedang
                                                    Proses</span>
                                            </div>
                                        @elseif($step['status'] === 'completed')
                                            <div class="mt-1">
                                                <span class="badge bg-primary" style="font-size: 0.7rem;">Selesai</span>
                                            </div>
                                        @elseif($step['status'] === 'pending')
                                            <div class="mt-1">
                                                <span class="badge bg-secondary" style="font-size: 0.7rem;">Belum
                                                    Aktif</span>
                                            </div>
                                        @endif

                                        @if ($step['date'])
                                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                                {{ \Carbon\Carbon::parse($step['date'])->format('d M Y') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mobile View - Vertical -->
                    <div class="d-block d-lg-none">
                        @foreach ($steps as $index => $step)
                            @php
                                $routeName = $tahapanRoutes[$step['name']] ?? null;
                                $stepStatus = $step['status']; // pending | active | completed
                                $isAccessible = in_array($stepStatus, ['active', 'completed']);
                                $routeUrl = $routeName && $isAccessible ? route($routeName, $permohonan) : '#';
                                $stepClass = 'step-' . $stepStatus;
                            @endphp

                            <a href="{{ $routeUrl }}"
                                class="d-flex mb-3 text-decoration-none step-item {{ $stepClass }}"
                                style="cursor: {{ $isAccessible ? 'pointer' : 'not-allowed' }}; pointer-events: {{ $isAccessible ? 'auto' : 'none' }};">
                                <div class="me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center step-circle"
                                        style="width: 40px; height: 40px;">
                                        @if ($step['status'] === 'completed')
                                            <i class='bx bx-check'></i>
                                        @elseif($step['status'] === 'active')
                                            <i class='bx bx-time-five'></i>
                                        @else
                                            <i class='bx bx-lock-alt'></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 step-name">
                                        {{ $step['name'] }}
                                        @if ($step['status'] === 'active')
                                            <span class="badge bg-warning ms-1">Sedang Proses</span>
                                        @elseif($step['status'] === 'completed')
                                            <span class="badge bg-primary ms-1">Selesai</span>
                                        @elseif($step['status'] === 'pending')
                                            <span class="badge bg-secondary ms-1">Belum Aktif</span>
                                        @endif
                                    </h6>
                                    @if ($step['date'])
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($step['date'])->format('d M Y') }}</small>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Umum Permohonan -->
        <div class="row">
            <!-- Left Column: Info Permohonan -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class='bx bx-info-circle me-2'></i>Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th width="45%">Kabupaten/Kota:</th>
                                        <td><strong>{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tahun:</th>
                                        <td>{{ $permohonan->tahun }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jenis Dokumen:</th>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th width="45%">Status:</th>
                                        <td>
                                            <span class="badge bg-{{ $permohonan->statusBadgeClass }}">
                                                {{ strtoupper($permohonan->status_akhir) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Submit:</th>
                                        <td>{{ $permohonan->submitted_at ? $permohonan->submitted_at->format('d M Y, H:i') : 'Belum disubmit' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat:</th>
                                        <td>{{ $permohonan->created_at->format('d M Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Informasi Jadwal Fasilitasi -->
                        @if ($permohonan->jadwalFasilitasi)
                            <div class="border-top pt-3">
                                <h6 class="mb-3"><i class='bx bx-calendar me-1'></i>Jadwal Fasilitasi</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="45%">Batas Permohonan:</th>
                                                <td>{{ $permohonan->jadwalFasilitasi->batas_permohonan ? \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->batas_permohonan)->format('d M Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Batas Upload:</th>
                                                <td>{{ $permohonan->jadwalFasilitasi->batas_upload ? \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->batas_upload)->format('d M Y') : '-' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="45%">Tanggal Mulai:</th>
                                                <td>{{ $permohonan->jadwalFasilitasi->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->tanggal_mulai)->format('d M Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Selesai:</th>
                                                <td>{{ $permohonan->jadwalFasilitasi->tanggal_selesai ? \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->tanggal_selesai)->format('d M Y') : '-' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Info Status -->
                        @if ($permohonan->status_akhir == 'belum')
                            <div class="alert alert-primary mt-3 mb-0">
                                <i class='bx bx-info-circle me-2'></i>
                                <strong>Status:</strong> Permohonan telah dibuat. Silakan lengkapi dokumen persyaratan dan
                                submit permohonan Anda.
                            </div>
                        @elseif ($permohonan->status_akhir == 'proses')
                            <div class="alert alert-info mt-3 mb-0">
                                <i class='bx bx-time-five me-2'></i>
                                <strong>Status:</strong> Permohonan telah disubmit dan sedang dalam proses.
                            </div>
                        @elseif ($permohonan->status_akhir == 'revisi')
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class='bx bx-error me-2'></i>
                                <strong>Status:</strong> Permohonan memerlukan revisi. Silakan periksa catatan dari
                                verifikator.
                            </div>
                        @elseif ($permohonan->status_akhir == 'selesai')
                            <div class="alert alert-success mt-3 mb-0">
                                <i class='bx bx-check-circle me-2'></i>
                                <strong>Status:</strong> Permohonan telah selesai diproses.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Timeline -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header">
                        <h6 class="mb-0"><i class='bx bx-time-five me-1'></i>Riwayat Tahapan</h6>
                    </div>
                    <div class="card-body p-3" style="max-height: 600px; overflow-y: auto;">
                        @php
                            // Ambil semua tahapan yang sudah dimulai (ada di permohonan_tahapan)
                            $tahapanHistory = $permohonan
                                ->tahapan()
                                ->with('masterTahapan')
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp

                        @if ($tahapanHistory->count() > 0)
                            @foreach ($tahapanHistory as $index => $history)
                                <div class="mb-3 pb-3 {{ $index < $tahapanHistory->count() - 1 ? 'border-bottom' : '' }}">
                                    <div class="mb-1">
                                        <strong
                                            style="font-size: 0.85rem;">{{ $history->masterTahapan->nama_tahapan }}</strong>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.8rem; line-height: 1.6;">
                                        Status:
                                        @if ($history->status === 'selesai')
                                            <span class="text-primary fw-semibold">Selesai</span>
                                        @elseif ($history->status === 'proses')
                                            <span class="text-warning fw-semibold">Sedang Proses</span>
                                        @elseif ($history->status === 'revisi')
                                            <span class="text-danger fw-semibold">Perlu Revisi</span>
                                        @else
                                            <span class="text-secondary">{{ ucfirst($history->status) }}</span>
                                        @endif

                                        <br>
                                        Dimulai: <span
                                            class="text-dark">{{ $history->created_at->format('d M Y, H:i') }}</span>

                                        @if ($history->status === 'selesai')
                                            <br>
                                            Selesai: <span
                                                class="text-dark">{{ $history->updated_at->format('d M Y, H:i') }}</span>
                                        @endif

                                        @if ($history->catatan)
                                            <br>
                                            <small class="text-info fst-italic">{{ $history->catatan }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class='bx bx-info-circle bx-lg mb-2 d-block'></i>
                                <p class="mb-0" style="font-size: 0.85rem;">Belum ada riwayat tahapan</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Notification helper
            function showNotification(type, message) {
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';

                const notification = $(`
                    <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed" 
                         role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        <i class='bx ${icon} me-2'></i>${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);

                $('body').append(notification);

                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        });
    </script>
@endpush
