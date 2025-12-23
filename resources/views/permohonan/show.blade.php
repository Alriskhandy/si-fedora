@extends('layouts.app')

@section('title', 'Detail Permohonan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Permohonan</h4>
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

        <!-- Progress Tracker -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Progress Tahapan</h5>
            </div>
            <div class="card-body">
                @php
                    $steps = $permohonan->getProgressSteps();
                    $currentIndex = $permohonan->getCurrentStepIndex();
                @endphp

                <!-- Desktop View - Horizontal -->
                <div class="d-none d-lg-block">
                    <div class="d-flex justify-content-between align-items-start position-relative"
                        style="max-width: 1000px; margin: 0 auto; padding: 0 50px;">
                        @foreach ($steps as $index => $step)
                            <div class="text-center position-relative" style="flex: 1; margin: 0 20px;">
                                <!-- Connector Line with Gradient -->
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

                                <!-- Step Circle with Gradient -->
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
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                                        style="width: 60px; height: 60px; font-size: 28px; background: {{ $circleGradient }}; box-shadow: {{ $circleShadow }}; transition: all 0.3s ease;">
                                        @if ($step['completed'])
                                            <i class='bx bx-check' style="font-weight: bold;"></i>
                                        @elseif($index === $currentIndex)
                                            <i class='bx bx-time-five'></i>
                                        @else
                                            <i class='bx bx-circle' style="font-size: 12px;"></i>
                                        @endif
                                    </div>
                                </div>

                                <!-- Step Info -->
                                <div class="mt-2">
                                    @php
                                        $textColor = $step['completed']
                                            ? '#1565C0'
                                            : ($index === $currentIndex
                                                ? '#2196F3'
                                                : '#9E9E9E');
                                        $textWeight = $step['completed'] || $index === $currentIndex ? '600' : '400';
                                    @endphp
                                    <h6 class="mb-1"
                                        style="font-size: 0.9rem; color: {{ $textColor }}; font-weight: {{ $textWeight }};">
                                        {{ $step['name'] }}
                                    </h6>

                                    @if ($index === $currentIndex && !$step['completed'])
                                        <div class="mt-2">
                                            <span class="badge bg-warning d-block mb-1">
                                                <i class='bx bx-time-five'></i> Sedang Berjalan
                                            </span>
                                            @if ($step['date'])
                                                <small class="badge bg-label-secondary">
                                                    {{ $step['date']->format('d M Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Mobile View - Vertical -->
                <div class="d-lg-none">
                    @foreach ($steps as $index => $step)
                        <div class="d-flex mb-4 position-relative">
                            <!-- Timeline Line with Gradient -->
                            @if ($index < count($steps) - 1)
                                @php
                                    $nextStep = $steps[$index + 1] ?? null;
                                    $lineGradient =
                                        $step['completed'] && ($nextStep && $nextStep['completed'])
                                            ? 'linear-gradient(180deg, #42A5F5 0%, #64B5F6 50%, #90CAF9 100%)'
                                            : ($step['completed']
                                                ? 'linear-gradient(180deg, #42A5F5 0%, #64B5F6 100%)'
                                                : '#E0E0E0');
                                @endphp
                                <div class="position-absolute"
                                    style="left: 29px; top: 60px; width: 4px; height: calc(100% - 20px); background: {{ $lineGradient }}; z-index: 0; border-radius: 2px; box-shadow: {{ $step['completed'] ? '0 3px 8px rgba(66, 165, 245, 0.25)' : 'none' }};">
                                </div>
                            @endif

                            <!-- Step Circle with Gradient -->
                            <div class="flex-shrink-0 position-relative" style="z-index: 1;">
                                @php
                                    $mobileCircleGradient = $step['completed']
                                        ? 'linear-gradient(135deg, #2196F3 0%, #42A5F5 50%, #64B5F6 100%)'
                                        : ($index === $currentIndex
                                            ? 'linear-gradient(135deg, #64B5F6 0%, #90CAF9 100%)'
                                            : 'linear-gradient(135deg, #E0E0E0 0%, #EEEEEE 100%)');
                                    $mobileCircleShadow = $step['completed']
                                        ? '0 8px 20px rgba(33, 150, 243, 0.35), 0 0 0 4px rgba(100, 181, 246, 0.2)'
                                        : ($index === $currentIndex
                                            ? '0 8px 20px rgba(100, 181, 246, 0.3), 0 0 0 4px rgba(144, 202, 249, 0.25)'
                                            : '0 4px 10px rgba(224, 224, 224, 0.3)');
                                @endphp
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                                    style="width: 60px; height: 60px; font-size: 28px; background: {{ $mobileCircleGradient }}; box-shadow: {{ $mobileCircleShadow }}; transition: all 0.3s ease;">
                                    @if ($step['completed'])
                                        <i class='bx bx-check' style="font-weight: bold;"></i>
                                    @elseif($index === $currentIndex)
                                        <i class='bx bx-time-five'></i>
                                    @else
                                        <i class='bx bx-circle' style="font-size: 12px;"></i>
                                    @endif
                                </div>
                            </div>

                            <!-- Step Content -->
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        @php
                                            $mobileTextColor = $step['completed']
                                                ? '#1565C0'
                                                : ($index === $currentIndex
                                                    ? '#2196F3'
                                                    : '#9E9E9E');
                                            $mobileTextWeight =
                                                $step['completed'] || $index === $currentIndex ? '600' : '400';
                                        @endphp
                                        <h6 class="mb-1"
                                            style="color: {{ $mobileTextColor }}; font-weight: {{ $mobileTextWeight }};">
                                            {{ $step['name'] }}
                                        </h6>

                                        @if ($index === $currentIndex && !$step['completed'])
                                            <span class="badge bg-warning mb-1">
                                                <i class='bx bx-time-five'></i> Sedang Berjalan
                                            </span>
                                            @if ($step['date'])
                                                <br>
                                                <small class="badge bg-label-secondary mt-1">
                                                    <i class='bx bx-calendar'></i> {{ $step['date']->format('d M Y') }}
                                                </small>
                                            @endif
                                        @endif
                                    </div>

                                    @if ($step['completed'])
                                        <i class='bx bx-check-circle fs-5' style="color: #2196F3;"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($permohonan->status_akhir === 'revisi')
                    <div class="alert alert-warning mt-4 mb-0">
                        <i class='bx bx-error-circle me-2'></i>
                        <strong>Perlu Revisi:</strong> Silakan perbaiki dokumen sesuai catatan verifikasi.
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                        <span
                            class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-sm-4"><small class="text-muted">Kabupaten/Kota</small></div>
                            <div class="col-sm-8">
                                <strong style="font-size: 0.9rem;">{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row mb-2">
                            <div class="col-sm-4"><small class="text-muted">Jenis Dokumen</small></div>
                            <div class="col-sm-8">
                                <span class="badge bg-primary"
                                    style="font-size: 0.7rem;">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row mb-2">
                            <div class="col-sm-4"><small class="text-muted">Tahun</small></div>
                            <div class="col-sm-8"><strong style="font-size: 0.9rem;">{{ $permohonan->tahun }}</strong>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row mb-2">
                            <div class="col-sm-4"><small class="text-muted">Jadwal Fasilitasi</small></div>
                            <div class="col-sm-8">
                                @if ($permohonan->jadwalFasilitasi)
                                    <strong style="font-size: 0.85rem;">
                                        {{ $permohonan->jadwalFasilitasi->tanggal_mulai->format('d M Y') }} s/d
                                        {{ $permohonan->jadwalFasilitasi->tanggal_selesai->format('d M Y') }}
                                    </strong>
                                    <br><small class="text-muted" style="font-size: 0.75rem;">
                                        Batas:
                                        {{ $permohonan->jadwalFasilitasi->batas_permohonan ? $permohonan->jadwalFasilitasi->batas_permohonan->format('d M Y') : '-' }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row mb-3">
                            <div class="col-sm-4"><small class="text-muted">Tanggal Dibuat</small></div>
                            <div class="col-sm-8">
                                <strong
                                    style="font-size: 0.85rem;">{{ $permohonan->created_at->format('d M Y H:i') }}</strong>
                            </div>
                        </div>

                        @if ($permohonan->status_akhir == 'belum' && auth()->user()->hasRole('pemohon'))
                            <hr class="my-3" style="border-top: 2px solid #dee2e6;">
                            <div class="mt-3">
                                <h6 class="mb-3" style="color: #1565C0; font-weight: 600;"><i
                                        class='bx bx-task me-1'></i>Aksi Permohonan</h6>
                                @php
                                    $dokumenBelumLengkap = $permohonan->permohonanDokumen
                                        ->where('is_ada', false)
                                        ->count();
                                    $totalDokumen = $permohonan->permohonanDokumen->count();
                                    $dokumenTerlengkapi = $totalDokumen - $dokumenBelumLengkap;
                                @endphp

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Kelengkapan Dokumen</small>
                                        <small
                                            class="text-muted"><strong>{{ $dokumenTerlengkapi }}/{{ $totalDokumen }}</strong></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $dokumenBelumLengkap == 0 ? 'bg-success' : 'bg-warning' }}"
                                            role="progressbar"
                                            style="width: {{ $totalDokumen > 0 ? ($dokumenTerlengkapi / $totalDokumen) * 100 : 0 }}%"
                                            aria-valuenow="{{ $dokumenTerlengkapi }}" aria-valuemin="0"
                                            aria-valuemax="{{ $totalDokumen }}">
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST"
                                    class="mb-2">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-success w-100 {{ $dokumenBelumLengkap > 0 ? 'disabled' : '' }}"
                                        {{ $dokumenBelumLengkap > 0 ? 'disabled' : '' }}
                                        onclick="return confirm('Yakin ingin mengirim permohonan ini? Setelah dikirim, dokumen tidak dapat diubah lagi.')">
                                        <i class='bx bx-send me-1'></i>
                                        {{ $dokumenBelumLengkap > 0 ? 'Lengkapi Dokumen' : 'Kirim Permohonan' }}
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('permohonan.index') }}" class="btn btn-outline-secondary w-100">
                                <i class='bx bx-arrow-back me-1'></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Timeline Card -->
                <div class="card">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #2196F3 0%, #42A5F5 100%); border: none; padding: 0.875rem 1.25rem;">
                        <h5 class="mb-0 text-white d-flex align-items-center">
                            <i class='bx bx-time-five me-2' style="font-size: 1.15rem;"></i>
                            <span style="font-weight: 600; font-size: 0.95rem;">Timeline Permohonan</span>
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 1.25rem; max-height: 600px; overflow-y: auto;">
                        <ul class="timeline mb-0">
                            <li class="timeline-item timeline-item-transparent pb-3">
                                <span class="timeline-point timeline-point-primary"
                                    style="background: #2196F3; border: 3px solid #E3F2FD; box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.1);"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-2">
                                        <h6 class="mb-1" style="color: #1565C0; font-weight: 600;">Dibuat</h6>
                                        <small class="d-block text-muted" style="font-size: 0.75rem;">
                                            <i
                                                class='bx bx-calendar me-1'></i>{{ $permohonan->created_at->format('d M Y, H:i') }}
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted" style="font-size: 0.8rem;">Permohonan dibuat oleh pemohon
                                    </p>
                                </div>
                            </li>
                            @if ($permohonan->submitted_at)
                                <li class="timeline-item timeline-item-transparent pb-3">
                                    <span class="timeline-point timeline-point-warning"
                                        style="background: #FF9800; border: 3px solid #FFF3E0; box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.1);"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-2">
                                            <h6 class="mb-1" style="color: #E65100; font-weight: 600;">Dokumen
                                                Dilengkapi & Dikirim</h6>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class='bx bx-calendar me-1'></i>{{ $permohonan->submitted_at->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Semua dokumen kelengkapan
                                            telah diupload dan permohonan dikirim untuk verifikasi
                                        </p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->status_akhir === 'selesai')
                                <li class="timeline-item timeline-item-transparent pb-3">
                                    <span class="timeline-point timeline-point-success"
                                        style="background: #4CAF50; border: 3px solid #E8F5E9; box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-2">
                                            <h6 class="mb-1" style="color: #2E7D32; font-weight: 600;">Selesai
                                                Verifikasi</h6>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class='bx bx-calendar me-1'></i>{{ $permohonan->updated_at->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Dokumen terverifikasi
                                            lengkap</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->status_akhir === 'revisi')
                                <li class="timeline-item timeline-item-transparent pb-1">
                                    <span class="timeline-point timeline-point-danger"
                                        style="background: #FF5722; border: 3px solid #FBE9E7; box-shadow: 0 0 0 4px rgba(255, 87, 34, 0.1);"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-2">
                                            <h6 class="mb-1" style="color: #D84315; font-weight: 600;">Perlu Revisi</h6>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class='bx bx-calendar me-1'></i>{{ $permohonan->updated_at->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Dokumen perlu diperbaiki</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->status === 'sent')
                                <li class="timeline-item timeline-item-transparent pb-3">
                                    <span class="timeline-point timeline-point-info"
                                        style="background: #00BCD4; border: 3px solid #E0F7FA; box-shadow: 0 0 0 4px rgba(0, 188, 212, 0.1);"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-2">
                                            <h6 class="mb-1" style="color: #00838F; font-weight: 600;">Undangan
                                                Pelaksanaan Dikirim</h6>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class='bx bx-calendar me-1'></i>{{ $permohonan->undanganPelaksanaan->created_at->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Undangan pelaksanaan
                                            fasilitasi telah dikirim</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->surat_penyampaian)
                                <li class="timeline-item timeline-item-transparent pb-3">
                                    <span class="timeline-point timeline-point-success"
                                        style="background: #4CAF50; border: 3px solid #E8F5E9; box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-2">
                                            <h6 class="mb-1" style="color: #2E7D32; font-weight: 600;">Surat Penyampaian Hasil</h6>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class='bx bx-calendar me-1'></i>{{ $permohonan->hasilFasilitasi->surat_tanggal->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Surat penyampaian hasil fasilitasi telah diupload</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->tindakLanjut)
                                <li class="timeline-item timeline-item-transparent pb-1">
                                    <span class="timeline-point timeline-point-success"
                                        style="background: #4CAF50; border: 3px solid #E8F5E9; box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-2">
                                            <h6 class="mb-1" style="color: #2E7D32; font-weight: 600;">Tindak Lanjut Hasil</h6>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class='bx bx-calendar me-1'></i>{{ $permohonan->tindakLanjut->tanggal_upload->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Laporan tindak lanjut telah diupload</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Undangan Fasilitasi -->
        @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->status === 'sent')
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center"
                            style="background: linear-gradient(135deg, #00BCD4 0%, #00ACC1 100%); border: none;">
                            <h5 class="mb-0 text-white">
                                <i class='bx bx-envelope-open me-2'></i>Undangan Pelaksanaan Fasilitasi
                            </h5>
                            <a href="{{ route('my-undangan.view', $permohonan->undanganPelaksanaan->id) }}"
                                class="btn btn-sm btn-light">
                                <i class='bx bx-show'></i> Lihat Detail
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Nomor Surat</label>
                                        <p class="mb-0 fw-bold">{{ $permohonan->undanganPelaksanaan->nomor_surat ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Tanggal Surat</label>
                                        <p class="mb-0 fw-bold">
                                            {{ $permohonan->undanganPelaksanaan->tanggal_surat ? $permohonan->undanganPelaksanaan->tanggal_surat->format('d M Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Waktu Pelaksanaan</label>
                                        <p class="mb-0 fw-bold">
                                            <i class='bx bx-calendar text-primary me-1'></i>
                                            {{ $permohonan->undanganPelaksanaan->waktu_pelaksanaan ? $permohonan->undanganPelaksanaan->waktu_pelaksanaan->format('d M Y, H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Tempat</label>
                                        <p class="mb-0 fw-bold">
                                            <i class='bx bx-map text-primary me-1'></i>
                                            {{ $permohonan->undanganPelaksanaan->tempat ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                                @if ($permohonan->undanganPelaksanaan->keterangan)
                                    <div class="col-12">
                                        <div class="mb-0">
                                            <label class="text-muted small">Keterangan</label>
                                            <p class="mb-0">{{ $permohonan->undanganPelaksanaan->keterangan }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('undangan-pelaksanaan.download', $permohonan) }}"
                                    class="btn btn-primary" target="_blank">
                                    <i class='bx bx-download'></i> Download Undangan
                                </a>
                                <a href="{{ route('my-undangan.view', $permohonan->undanganPelaksanaan->id) }}"
                                    class="btn btn-outline-primary">
                                    <i class='bx bx-show'></i> Lihat Detail Lengkap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Dokumen Persyaratan -->
        <div class="row mt-4">
            <div class="col-12">
                <!-- Surat Permohonan -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class='bx bx-file-blank me-2'></i>Surat Permohonan
                        </h5>
                        @if ($permohonan->status_akhir == 'belum')
                            <span class="badge bg-label-info">Wajib</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($permohonan->status_akhir == 'belum')
                            <div class="alert alert-info mb-3">
                                <i class='bx bx-info-circle me-2'></i>
                                Upload surat permohonan resmi dari Kabupaten/Kota yang ditujukan kepada Kepala Badan.
                            </div>
                        @endif

                        @php
                            $suratPermohonan = $permohonan->permohonanDokumen->first(function ($dok) {
                                return $dok->masterKelengkapan &&
                                    $dok->masterKelengkapan->kategori === 'surat_permohonan';
                            });
                        @endphp

                        @if ($suratPermohonan)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th width="40%">Nama Dokumen</th>
                                            <th width="10%">File</th>
                                            <th width="10%">Status Upload</th>
                                            <th width="11%">Status Verifikasi</th>
                                            <th width="19%">Catatan Verifikasi</th>
                                            @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                                                <th width="10%">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $suratPermohonan->masterKelengkapan->nama_dokumen ?? 'Surat Permohonan' }}</strong>
                                                    @if ($suratPermohonan->masterKelengkapan && $suratPermohonan->masterKelengkapan->wajib)
                                                        <span class="badge bg-label-danger ms-1"
                                                            style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">Wajib</span>
                                                    @endif
                                                </div>
                                                @if ($suratPermohonan->masterKelengkapan && $suratPermohonan->masterKelengkapan->deskripsi)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class='bx bx-info-circle'></i>
                                                        {{ $suratPermohonan->masterKelengkapan->deskripsi }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($suratPermohonan->file_path)
                                                    <a href="{{ asset('storage/' . $suratPermohonan->file_path) }}"
                                                        target="_blank" class="btn btn-xs btn-outline-primary"
                                                        style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                        <i class="bx bx-download" style="font-size: 0.875rem;"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="badge bg-label-warning"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-upload' style="font-size: 0.75rem;"></i> Belum
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($suratPermohonan->is_ada)
                                                    <span class="badge bg-label-success"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-check' style="font-size: 0.75rem;"></i> Tersedia
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-x' style="font-size: 0.75rem;"></i> Belum
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($suratPermohonan->status_verifikasi === 'verified')
                                                    <span class="badge bg-success"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-check-circle' style="font-size: 0.75rem;"></i>
                                                        Sesuai
                                                    </span>
                                                @elseif($suratPermohonan->status_verifikasi === 'revision')
                                                    <span class="badge bg-danger"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-x-circle' style="font-size: 0.75rem;"></i> Revisi
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-time' style="font-size: 0.75rem;"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($suratPermohonan->catatan_verifikasi)
                                                    <small
                                                        class="text-{{ $suratPermohonan->status_verifikasi === 'verified' ? 'success' : 'danger' }}">
                                                        <i
                                                            class='bx bx-{{ $suratPermohonan->status_verifikasi === 'verified' ? 'check-circle' : 'error-circle' }}'></i>
                                                        {{ $suratPermohonan->catatan_verifikasi }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                                                <td>
                                                    @if ($suratPermohonan->status_verifikasi === 'verified')
                                                        <span class="badge bg-success">
                                                            <i class='bx bx-lock'></i> Terverifikasi
                                                        </span>
                                                    @else
                                                        <form
                                                            action="{{ route('permohonan-dokumen.upload', $suratPermohonan) }}"
                                                            method="POST" enctype="multipart/form-data"
                                                            class="upload-dokumen-form mb-0"
                                                            data-dokumen-id="{{ $suratPermohonan->id }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="file" name="file"
                                                                class="file-input d-none" accept=".pdf,.xls,.xlsx"
                                                                required>
                                                            <button type="button"
                                                                class="btn btn-sm btn-{{ $suratPermohonan->status_verifikasi === 'revision' ? 'warning' : 'primary' }} btn-upload-trigger">
                                                                <i class="bx bx-upload"></i>
                                                                {{ $suratPermohonan->status_verifikasi === 'revision' ? 'Upload Ulang' : 'Upload' }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class='bx bx-folder-open bx-lg mb-2 d-block'></i>
                                Dokumen surat permohonan belum tersedia
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Kelengkapan Verifikasi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-folder-open me-2'></i>Kelengkapan Verifikasi
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($permohonan->status_akhir == 'belum')
                            <div class="alert alert-info">
                                <i class='bx bx-info-circle me-2'></i>
                                Silakan upload semua dokumen kelengkapan verifikasi sebelum mengirim permohonan.
                            </div>
                        @endif

                        @php
                            $kelengkapanVerifikasi = $permohonan->permohonanDokumen->filter(function ($dok) {
                                return $dok->masterKelengkapan &&
                                    $dok->masterKelengkapan->kategori === 'kelengkapan_verifikasi';
                            });
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th width="3%">No</th>
                                        <th width="37%">Nama Dokumen</th>
                                        <th width="10%">File</th>
                                        <th width="10%">Status Upload</th>
                                        <th width="11%">Status Verifikasi</th>
                                        <th width="19%">Catatan Verifikasi</th>
                                        @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                                            <th width="10%">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelengkapanVerifikasi as $index => $dokumen)
                                        <tr>
                                            <td>{{ $index }}</td>
                                            <td>
                                                <div>
                                                    <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen Kelengkapan' }}</strong>
                                                </div>
                                                @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->deskripsi)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class='bx bx-info-circle'></i>
                                                        {{ $dokumen->masterKelengkapan->deskripsi }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($dokumen->file_path)
                                                    <a href="{{ asset('storage/' . $dokumen->file_path) }}"
                                                        target="_blank" class="btn btn-xs btn-outline-primary"
                                                        style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                        <i class="bx bx-download" style="font-size: 0.875rem;"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="badge bg-label-warning"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-upload' style="font-size: 0.75rem;"></i> Belum
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($dokumen->is_ada)
                                                    <span class="badge bg-label-success"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-check' style="font-size: 0.75rem;"></i> Tersedia
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-x' style="font-size: 0.75rem;"></i> Belum
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($dokumen->status_verifikasi === 'verified')
                                                    <span class="badge bg-success"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-check-circle' style="font-size: 0.75rem;"></i>
                                                        Sesuai
                                                    </span>
                                                @elseif($dokumen->status_verifikasi === 'revision')
                                                    <span class="badge bg-danger"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-x-circle' style="font-size: 0.75rem;"></i> Revisi
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary"
                                                        style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <i class='bx bx-time' style="font-size: 0.75rem;"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->catatan_verifikasi)
                                                    <small
                                                        class="text-{{ $dokumen->status_verifikasi === 'verified' ? 'success' : 'danger' }}">
                                                        <i
                                                            class='bx bx-{{ $dokumen->status_verifikasi === 'verified' ? 'check-circle' : 'error-circle' }}'></i>
                                                        {{ $dokumen->catatan_verifikasi }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                                                <td>
                                                    @if ($dokumen->status_verifikasi === 'verified')
                                                        <span class="badge bg-success">
                                                            <i class='bx bx-lock'></i> Terverifikasi
                                                        </span>
                                                    @else
                                                        <form action="{{ route('permohonan-dokumen.upload', $dokumen) }}"
                                                            method="POST" enctype="multipart/form-data"
                                                            class="upload-dokumen-form mb-0"
                                                            data-dokumen-id="{{ $dokumen->id }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="file" name="file"
                                                                class="file-input d-none" accept=".pdf,.xls,.xlsx"
                                                                required>
                                                            <button type="button"
                                                                class="btn btn-sm btn-{{ $dokumen->status_verifikasi === 'revision' ? 'warning' : 'primary' }} btn-upload-trigger">
                                                                <i class="bx bx-upload"></i>
                                                                {{ $dokumen->status_verifikasi === 'revision' ? 'Upload Ulang' : 'Upload' }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi' ? '7' : '6' }}"
                                                class="text-center py-4">
                                                <i class='bx bx-folder-open bx-lg text-muted mb-2 d-block'></i>
                                                <p class="text-muted mb-0">Tidak ada dokumen kelengkapan</p>
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

@push('scripts')
    <script>
        $(document).ready(function() {
            // Trigger file input when upload button clicked
            $('.btn-upload-trigger').on('click', function() {
                $(this).siblings('.file-input').click();
            });

            // Auto submit when file selected
            $('.file-input').on('change', function() {
                if (this.files.length > 0) {
                    const form = $(this).closest('form');
                    const button = form.find('.btn-upload-trigger');
                    const buttonText = button.html();

                    // Disable button and show loading
                    button.prop('disabled', true).html(
                        '<i class="bx bx-loader bx-spin"></i> Upload...');

                    const formData = new FormData(form[0]);

                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                // Show success checkmark
                                button.removeClass('btn-primary').addClass('btn-success').html(
                                    '<i class="bx bx-check-circle"></i> Berhasil'
                                );

                                // Reload after 1 second
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            // Show error icon
                            button.removeClass('btn-primary').addClass('btn-danger').html(
                                '<i class="bx bx-x-circle"></i> Gagal'
                            );

                            // Reset button after 2 seconds
                            setTimeout(function() {
                                button.prop('disabled', false)
                                    .removeClass('btn-danger')
                                    .addClass('btn-primary')
                                    .html(buttonText);
                            }, 2000);

                            // Log error to console
                            let errorMessage = 'Terjadi kesalahan saat upload';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMessage = Object.values(xhr.responseJSON.errors).flat()
                                    .join(', ');
                            }
                            console.error('Upload error:', errorMessage);
                        }
                    });
                }
            });
        });
    </script>
@endpush
