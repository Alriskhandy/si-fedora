@extends('layouts.app')

@section('title', 'Detail Laporan Verifikasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Laporan Hasil Verifikasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('laporan-verifikasi.index') }}">Laporan Verifikasi</a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <div>
                @if ($laporan->status_kelengkapan == 'tidak_lengkap')
                    <a href="{{ route('laporan-verifikasi.create', $permohonan) }}" class="btn btn-warning me-2">
                        <i class='bx bx-edit'></i> Perbaiki Laporan
                    </a>
                @endif
                <a href="{{ route('laporan-verifikasi.download', $permohonan) }}" class="btn btn-success me-2">
                    <i class='bx bx-download'></i> Download PDF
                </a>
                <a href="{{ route('laporan-verifikasi.index') }}" class="btn btn-secondary">
                    <i class='bx bx-arrow-back'></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Informasi Permohonan -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Kabupaten/Kota</label>
                            <p class="fw-bold mb-0">{{ $permohonan->kabupatenKota->nama ?? '-' }}</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Jenis Dokumen</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ strtoupper($permohonan->jenisDokumen->nama) }}</span>
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Tahun</label>
                            <p class="fw-bold mb-0">{{ $permohonan->tahun }}</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span
                                    class="badge bg-{{ $laporan->status_kelengkapan == 'lengkap' ? 'success' : 'warning' }}">
                                    {{ $laporan->status_kelengkapan == 'lengkap' ? 'Lengkap' : 'Tidak Lengkap' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistik Dokumen -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Statistik Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h2 class="mb-0">{{ $laporan->persentase_verified }}%</h2>
                            <small class="text-muted">Persentase Terverifikasi</small>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $laporan->persentase_verified }}%">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Total Dokumen</span>
                            <h4 class="mb-0">{{ $laporan->total_dokumen }}</h4>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-success">
                                <i class='bx bx-check-circle'></i> Terverifikasi
                            </span>
                            <h5 class="mb-0 text-success">{{ $laporan->jumlah_dokumen_verified }}</h5>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-warning">
                                <i class='bx bx-error-circle'></i> Perlu Revisi
                            </span>
                            <h5 class="mb-0 text-warning">{{ $laporan->jumlah_dokumen_revision }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Laporan -->
            <div class="col-lg-8">
                <!-- Informasi Laporan -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Laporan</h5>
                        <span class="badge bg-primary">
                            <i class='bx bx-calendar'></i> {{ $laporan->tanggal_laporan->format('d M Y, H:i') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Dibuat Oleh</label>
                            <p class="mb-0">
                                <i class='bx bx-user'></i> {{ $laporan->pembuatLaporan->name ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Verifikasi -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan Hasil Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($laporan->ringkasan_verifikasi)) !!}
                        </div>
                    </div>
                </div>

                <!-- Catatan Admin -->
                @if ($laporan->catatan_admin)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Catatan Tambahan Admin</h5>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($laporan->catatan_admin)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Status Kelengkapan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Kelengkapan Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div
                            class="alert alert-{{ $laporan->status_kelengkapan == 'lengkap' ? 'success' : 'warning' }} mb-0">
                            <div class="d-flex align-items-center">
                                <i
                                    class='bx {{ $laporan->status_kelengkapan == 'lengkap' ? 'bx-check-circle' : 'bx-error-circle' }} fs-3 me-3'></i>
                                <div>
                                    <h5 class="mb-1">
                                        {{ $laporan->status_kelengkapan == 'lengkap' ? 'Dokumen Lengkap' : 'Dokumen Tidak Lengkap' }}
                                    </h5>
                                    <p class="mb-0">
                                        @if ($laporan->status_kelengkapan == 'lengkap')
                                            Semua dokumen telah terverifikasi dengan baik dan dapat dilanjutkan ke tahap
                                            berikutnya.
                                        @else
                                            Ada beberapa dokumen yang perlu diperbaiki atau dilengkapi.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
