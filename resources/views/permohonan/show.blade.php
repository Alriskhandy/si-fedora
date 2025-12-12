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
                                <!-- Connector Line -->
                                @if ($index < count($steps) - 1)
                                    <div class="position-absolute top-0 start-50 translate-middle-y"
                                        style="left: 50%; right: -100%; width: calc(200% + 40px); height: 2px; background-color: {{ $step['completed'] ? '#696cff' : '#e0e0e0' }}; z-index: 0; margin-top: 30px;">
                                    </div>
                                @endif

                                <!-- Step Circle with Number -->
                                <div class="position-relative d-inline-block" style="z-index: 1;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ $step['completed'] ? 'bg-primary' : 'bg-secondary' }} text-white fw-bold mx-auto mb-3"
                                        style="width: 60px; height: 60px; font-size: 24px;">
                                        {{ $index + 1 }}
                                    </div>
                                </div>

                                <!-- Step Info -->
                                <div class="mt-2">
                                    <h6 class="mb-1 {{ $step['completed'] ? 'text-dark fw-bold' : 'text-muted' }}"
                                        style="font-size: 0.9rem;">
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
                            <!-- Timeline Line -->
                            @if ($index < count($steps) - 1)
                                <div class="position-absolute"
                                    style="left: 29px; top: 60px; width: 2px; height: calc(100% - 20px); background-color: {{ $step['completed'] ? '#696cff' : '#e0e0e0' }}; z-index: 0;">
                                </div>
                            @endif

                            <!-- Step Circle with Number -->
                            <div class="flex-shrink-0 position-relative" style="z-index: 1;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $step['completed'] ? 'bg-primary' : 'bg-secondary' }} text-white fw-bold"
                                    style="width: 60px; height: 60px; font-size: 24px;">
                                    {{ $index + 1 }}
                                </div>
                            </div>

                            <!-- Step Content -->
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 {{ $step['completed'] ? 'text-dark fw-bold' : 'text-muted' }}">
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
                                        <i class='bx bx-check-circle text-primary fs-5'></i>
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
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Kabupaten/Kota</label></div>
                            <div class="col-sm-8">
                                <strong>{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Jenis Dokumen</label></div>
                            <div class="col-sm-8">
                                <span class="badge bg-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Tahun</label></div>
                            <div class="col-sm-8"><strong>{{ $permohonan->tahun }}</strong></div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Jadwal Fasilitasi</label></div>
                            <div class="col-sm-8">
                                @if ($permohonan->jadwalFasilitasi)
                                    <strong>
                                        {{ $permohonan->jadwalFasilitasi->tanggal_mulai->format('d M Y') }} s/d
                                        {{ $permohonan->jadwalFasilitasi->tanggal_selesai->format('d M Y') }}
                                    </strong>
                                    <br><small class="text-muted">
                                        Batas Permohonan:
                                        {{ $permohonan->jadwalFasilitasi->batas_permohonan ? $permohonan->jadwalFasilitasi->batas_permohonan->format('d M Y') : '-' }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Tanggal Dibuat</label></div>
                            <div class="col-sm-8">
                                <strong>{{ $permohonan->created_at->format('d M Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Timeline Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Timeline</h5>
                    </div>
                    <div class="card-body">
                        <ul class="timeline">
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Dibuat</h6>
                                        <small
                                            class="text-muted">{{ $permohonan->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <p class="mb-0 small">Permohonan dibuat</p>
                                </div>
                            </li>
                            @if ($permohonan->submitted_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-warning"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Diajukan</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->submitted_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Menunggu verifikasi</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->status_akhir === 'selesai')
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Selesai Verifikasi</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->updated_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Dokumen terverifikasi lengkap</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->status_akhir === 'revisi')
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-warning"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Perlu Revisi</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->updated_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Dokumen perlu diperbaiki</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Action Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Aksi</h5>
                    </div>
                    <div class="card-body">
                        @if ($permohonan->status_akhir == 'belum' && auth()->user()->hasRole('pemohon'))
                            @php
                                $dokumenBelumLengkap = $permohonan->permohonanDokumen->where('is_ada', false)->count();
                                $totalDokumen = $permohonan->permohonanDokumen->count();
                                $dokumenTerlengkapi = $totalDokumen - $dokumenBelumLengkap;
                            @endphp

                            <!-- Progress Upload Dokumen -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Kelengkapan Dokumen</small>
                                    <small class="text-muted">{{ $dokumenTerlengkapi }}/{{ $totalDokumen }}</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $dokumenBelumLengkap == 0 ? 'bg-success' : 'bg-warning' }}"
                                        role="progressbar"
                                        style="width: {{ $totalDokumen > 0 ? ($dokumenTerlengkapi / $totalDokumen) * 100 : 0 }}%"
                                        aria-valuenow="{{ $dokumenTerlengkapi }}" aria-valuemin="0"
                                        aria-valuemax="{{ $totalDokumen }}">
                                    </div>
                                </div>
                            </div>

                            @if ($dokumenBelumLengkap > 0)
                                <div class="alert alert-warning alert-dismissible mb-3" role="alert">
                                    <i class='bx bx-info-circle me-2'></i>
                                    <strong>Perhatian!</strong><br>
                                    Masih ada <strong>{{ $dokumenBelumLengkap }} dokumen</strong> yang belum diupload.
                                    Silakan lengkapi semua dokumen sebelum mengirim permohonan.
                                </div>
                            @else
                                <div class="alert alert-success mb-3" role="alert">
                                    <i class='bx bx-check-circle me-2'></i>
                                    <strong>Dokumen Lengkap!</strong><br>
                                    Semua dokumen sudah diupload. Anda dapat mengirim permohonan sekarang.
                                </div>
                            @endif

                            <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit"
                                    class="btn btn-success w-100 {{ $dokumenBelumLengkap > 0 ? 'disabled' : '' }}"
                                    {{ $dokumenBelumLengkap > 0 ? 'disabled' : '' }}
                                    onclick="return confirm('Yakin ingin mengirim permohonan ini? Setelah dikirim, dokumen tidak dapat diubah lagi.')">
                                    <i class='bx bx-send me-1'></i>
                                    {{ $dokumenBelumLengkap > 0 ? 'Lengkapi Dokumen Terlebih Dahulu' : 'Kirim Permohonan' }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('permohonan.index') }}" class="btn btn-outline-secondary w-100">
                            <i class='bx bx-arrow-back me-1'></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

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
                                    <thead>
                                        <tr>
                                            <th width="25%">Nama Dokumen</th>
                                            <th width="20%">File</th>
                                            <th width="12%">Status Upload</th>
                                            <th width="15%">Status Verifikasi</th>
                                            <th width="18%">Catatan Verifikasi</th>
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
                                                        <span class="badge badge-sm bg-label-danger ms-1">Wajib</span>
                                                    @endif
                                                </div>
                                                @if ($suratPermohonan->masterKelengkapan && $suratPermohonan->masterKelengkapan->deskripsi)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class='bx bx-info-circle'></i>
                                                        {{ $suratPermohonan->masterKelengkapan->deskripsi }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($suratPermohonan->file_path)
                                                    <a href="{{ asset('storage/' . $suratPermohonan->file_path) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-download me-1"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="badge bg-label-warning">
                                                        <i class='bx bx-upload'></i> Belum upload
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($suratPermohonan->is_ada)
                                                    <span class="badge bg-label-success">
                                                        <i class='bx bx-check'></i> Tersedia
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger">
                                                        <i class='bx bx-x'></i> Belum Upload
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($suratPermohonan->status_verifikasi === 'verified')
                                                    <span class="badge bg-success">
                                                        <i class='bx bx-check-circle'></i> Sesuai
                                                    </span>
                                                @elseif($suratPermohonan->status_verifikasi === 'revision')
                                                    <span class="badge bg-danger">
                                                        <i class='bx bx-x-circle'></i> Perlu Revisi
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class='bx bx-time'></i> Pending
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
                                                                class="file-input d-none" accept=".pdf,.doc,.docx"
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
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="23%">Nama Dokumen</th>
                                        <th width="17%">File</th>
                                        <th width="10%">Status Upload</th>
                                        <th width="13%">Status Verifikasi</th>
                                        <th width="17%">Catatan Verifikasi</th>
                                        @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                                            <th width="10%">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelengkapanVerifikasi as $index => $dokumen)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>
                                                    <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen Kelengkapan' }}</strong>
                                                    @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->wajib)
                                                        <span class="badge badge-sm bg-label-danger ms-1">Wajib</span>
                                                    @endif
                                                </div>
                                                @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->deskripsi)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class='bx bx-info-circle'></i>
                                                        {{ $dokumen->masterKelengkapan->deskripsi }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->file_path)
                                                    <a href="{{ asset('storage/' . $dokumen->file_path) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-download me-1"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="badge bg-label-warning">
                                                        <i class='bx bx-upload'></i> Belum upload
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->is_ada)
                                                    <span class="badge bg-label-success">
                                                        <i class='bx bx-check'></i> Tersedia
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger">
                                                        <i class='bx bx-x'></i> Belum Upload
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->status_verifikasi === 'verified')
                                                    <span class="badge bg-success">
                                                        <i class='bx bx-check-circle'></i> Sesuai
                                                    </span>
                                                @elseif($dokumen->status_verifikasi === 'revision')
                                                    <span class="badge bg-danger">
                                                        <i class='bx bx-x-circle'></i> Perlu Revisi
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class='bx bx-time'></i> Pending
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
                                                                class="file-input d-none" accept=".pdf,.doc,.docx"
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
