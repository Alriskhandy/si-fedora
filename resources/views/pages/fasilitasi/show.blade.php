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

        /* Active step highlight */
        .active-step .step-circle {
            box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.3) !important;
            transform: scale(1.05);
        }

        .active-step .step-name {
            color: #1565C0 !important;
            font-weight: 600 !important;
        }

        /* Fix button primary active/focus state */
        .btn-primary:active,
        .btn-primary.active,
        .btn-primary:focus,
        .btn-primary.focus {
            background-color: #7bc4e3 !important;
            border-color: #7bc4e3 !important;
            color: #0d3b4d !important;
            box-shadow: 0 0 0 0.2rem rgba(160, 217, 239, 0.5) !important;
        }

        .btn-primary:not(:disabled):not(.disabled):active,
        .btn-primary:not(:disabled):not(.disabled).active {
            background-color: #5eb5d4 !important;
            border-color: #5eb5d4 !important;
        }

        .step-clickable:hover .step-circle {
            transform: scale(1.1);
            box-shadow: 0 10px 25px rgba(33, 150, 243, 0.4) !important;
        }

        .step-clickable:hover .step-name {
            color: #1976D2 !important;
        }

        .step-disabled .step-circle {
            background: #F5F5F5 !important;
            border: 2px solid #E0E0E0 !important;
            color: #BDBDBD !important;
        }

        .step-disabled .step-name {
            color: #9E9E9E !important;
        }

        .step-item {
            transition: all 0.3s ease;
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
                            'Hasil Fasilitasi' => 'permohonan.tahapan.hasil',
                            'Tindak Lanjut' => 'permohonan.tahapan.tindak-lanjut',
                            'Penetapan PERDA' => 'permohonan.tahapan.penetapan',
                        ];
                    @endphp

                    <!-- Desktop View - Horizontal -->
                    <div class="d-none d-lg-block">
                        <div class="d-flex justify-content-between align-items-start position-relative"
                            style="max-width: 1000px; margin: 0 auto; padding: 0 50px;">
                            @foreach ($steps as $index => $step)
                                @php
                                    $routeName = $tahapanRoutes[$step['name']] ?? null;
                                    // Step Permohonan dan Verifikasi selalu bisa diklik
                                    $isDisabled =
                                        $index > $currentIndex &&
                                        !in_array($step['name'], ['Permohonan', 'Verifikasi']);
                                    $isActive = $index === $currentIndex;
                                    $routeUrl = $routeName && !$isDisabled ? route($routeName, $permohonan) : '#';
                                @endphp

                                <a href="{{ $routeUrl }}"
                                    class="text-decoration-none step-item {{ $isDisabled ? 'step-disabled' : 'step-clickable' }}"
                                    style="flex: 1; margin: 0 20px; cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }}; pointer-events: {{ $isDisabled ? 'none' : 'auto' }};">
                                    @if ($index < count($steps) - 1)
                                        @php
                                            $nextStep = $steps[$index + 1] ?? null;
                                            $isConnectorActive =
                                                $step['completed'] && ($nextStep && $nextStep['completed']);
                                            $gradientColor = $isConnectorActive
                                                ? 'linear-gradient(90deg, #42A5F5 0%, #64B5F6 50%, #90CAF9 100%)'
                                                : ($step['completed']
                                                    ? 'linear-gradient(90deg, #42A5F5 0%, #64B5F6 100%)'
                                                    : '#E0E0E0');
                                        @endphp
                                        <div class="position-absolute top-0 start-50 translate-middle-y"
                                            style="left: 50%; right: -100%; width: calc(200% + 40px); height: 4px; background: {{ $gradientColor }}; z-index: 0; margin-top: 30px; border-radius: 2px; box-shadow: {{ $step['completed'] ? '0 3px 8px rgba(66, 165, 245, 0.25)' : 'none' }};">
                                        </div>
                                    @endif

                                    <div class="position-relative d-inline-block" style="z-index: 1;">
                                        @php
                                            $circleGradient = $step['completed']
                                                ? 'linear-gradient(135deg, #2196F3 0%, #42A5F5 50%, #64B5F6 100%)'
                                                : ($index === $currentIndex
                                                    ? 'linear-gradient(135deg, #64B5F6 0%, #90CAF9 100%)'
                                                    : 'linear-gradient(135deg, #E0E0E0 0%, #EEEEEE 100%)');
                                            $circleShadow = $step['completed']
                                                ? '0 8px 20px rgba(33, 150, 243, 0.35), 0 0 0 4px rgba(100, 181, 246, 0.2)'
                                                : ($index === $currentIndex
                                                    ? '0 8px 20px rgba(100, 181, 246, 0.3), 0 0 0 4px rgba(144, 202, 249, 0.25)'
                                                    : '0 4px 10px rgba(224, 224, 224, 0.3)');
                                        @endphp
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3 step-circle"
                                            style="width: 60px; height: 60px; font-size: 28px; background: {{ $circleGradient }}; box-shadow: {{ $circleShadow }}; transition: all 0.3s ease;">
                                            @if ($step['completed'])
                                                <i class='bx bx-check' style="font-weight: bold;"></i>
                                            @elseif($index === $currentIndex)
                                                <i class='bx bx-time-five'></i>
                                            @else
                                                <i class='bx bx-lock-alt' style="font-size: 12px;"></i>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-2 text-center">
                                        @php
                                            $textColor = $step['completed']
                                                ? '#1565C0'
                                                : ($index === $currentIndex
                                                    ? '#2196F3'
                                                    : '#9E9E9E');
                                            $textWeight =
                                                $step['completed'] || $index === $currentIndex ? '600' : '400';
                                        @endphp
                                        <h6 class="mb-1 step-name"
                                            style="font-size: 0.9rem; color: {{ $textColor }}; font-weight: {{ $textWeight }};">
                                            {{ $step['name'] }}
                                        </h6>

                                        @if ($index === $currentIndex && !$step['completed'])
                                            <div class="mt-2">
                                                <span class="badge bg-warning d-block mb-1">Sedang Berjalan</span>
                                            </div>
                                        @endif

                                        @if ($step['date'])
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                {{ \Carbon\Carbon::parse($step['date'])->format('d M Y') }}
                                            </small>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mobile View - Vertical -->
                    <div class="d-block d-lg-none">
                        @foreach ($steps as $index => $step)
                            @php
                                $routeName = $tahapanRoutes[$step['name']] ?? null;
                                // Step Permohonan dan Verifikasi selalu bisa diklik
                                $isDisabled =
                                    $index > $currentIndex && !in_array($step['name'], ['Permohonan', 'Verifikasi']);
                                $routeUrl = $routeName && !$isDisabled ? route($routeName, $permohonan) : '#';
                            @endphp

                            <a href="{{ $routeUrl }}"
                                class="d-flex mb-3 text-decoration-none step-item {{ $isDisabled ? 'step-disabled' : 'step-clickable' }}"
                                style="cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }}; pointer-events: {{ $isDisabled ? 'none' : 'auto' }};">
                                <div class="me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center step-circle {{ $step['completed'] ? 'bg-primary text-white' : ($index === $currentIndex ? 'bg-info text-white' : 'bg-light text-muted') }}"
                                        style="width: 40px; height: 40px;">
                                        @if ($step['completed'])
                                            <i class='bx bx-check'></i>
                                        @elseif($index === $currentIndex)
                                            <i class='bx bx-time-five'></i>
                                        @else
                                            <i class='bx bx-circle' style="font-size: 8px;"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6
                                        class="mb-0 step-name {{ $step['completed'] ? 'text-primary fw-semibold' : ($index === $currentIndex ? 'text-info fw-semibold' : 'text-muted') }}">
                                        {{ $step['name'] }}
                                        @if ($index === $currentIndex && !$step['completed'])
                                            <span class="badge bg-warning ms-1">Aktif</span>
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
                        <h6 class="mb-0"><i class='bx bx-time-five me-1'></i>Status Fasilitasi / Evaluasi</h6>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        @if ($permohonan->activityLogs && $permohonan->activityLogs->count() > 0)
                            <div class="timeline">
                                @foreach ($permohonan->activityLogs->sortByDesc('created_at') as $log)
                                    <div class="timeline-item mb-3">
                                        <div class="d-flex">
                                            <div class="timeline-icon me-2">
                                                <i
                                                    class='bx bx-{{ $log->event == 'created' ? 'plus-circle' : ($log->event == 'updated' ? 'edit' : 'info-circle') }} text-primary'></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <small class="text-muted d-block">
                                                    {{ $log->created_at->diffForHumans() }}
                                                </small>
                                                <p class="mb-0">{{ $log->description }}</p>
                                                @if ($log->causer)
                                                    <small class="text-muted">oleh
                                                        {{ $log->causer->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class='bx bx-time-five bx-lg mb-2 d-block'></i>
                                <p class="mb-0">Belum ada aktivitas</p>
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
