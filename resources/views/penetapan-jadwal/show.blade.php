@extends('layouts.app')

@section('title', 'Detail Penetapan Jadwal')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Penetapan Jadwal Fasilitasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penetapan-jadwal.index') }}">Penetapan Jadwal</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
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
                                <span class="badge bg-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Tahun</label>
                            <p class="fw-bold mb-0">{{ $permohonan->tahun }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Jadwal -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Jadwal</h5>
                    </div>
                    <div class="card-body">
                        @if ($penetapan->isBelumMulai())
                            <div class="alert alert-info mb-0">
                                <i class='bx bx-time-five'></i>
                                <strong>Belum Dimulai</strong>
                                <p class="mb-0 small mt-2">
                                    Fasilitasi akan dimulai {{ $penetapan->tanggal_mulai->diffForHumans() }}
                                </p>
                            </div>
                        @elseif($penetapan->isAktif())
                            <div class="alert alert-success mb-0">
                                <i class='bx bx-check-circle'></i>
                                <strong>Sedang Berlangsung</strong>
                                <p class="mb-0 small mt-2">
                                    Fasilitasi sedang berlangsung dan akan selesai
                                    {{ $penetapan->tanggal_selesai->diffForHumans() }}
                                </p>
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0">
                                <i class='bx bx-calendar-check'></i>
                                <strong>Sudah Selesai</strong>
                                <p class="mb-0 small mt-2">
                                    Fasilitasi telah selesai {{ $penetapan->tanggal_selesai->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detail Jadwal -->
            <div class="col-lg-8">
                <!-- Informasi Jadwal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-calendar'></i> Jadwal Fasilitasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="text-muted small">Tanggal Mulai</label>
                                <h5 class="mb-0">
                                    <i class='bx bx-calendar-event text-primary'></i>
                                    {{ $penetapan->tanggal_mulai->format('d M Y') }}
                                </h5>
                                <small class="text-muted">{{ $penetapan->tanggal_mulai->format('l') }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Tanggal Selesai</label>
                                <h5 class="mb-0">
                                    <i class='bx bx-calendar-check text-success'></i>
                                    {{ $penetapan->tanggal_selesai->format('d M Y') }}
                                </h5>
                                <small class="text-muted">{{ $penetapan->tanggal_selesai->format('l') }}</small>
                            </div>
                        </div>

                        <div class="alert alert-light">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-time text-primary fs-4 me-3'></i>
                                <div>
                                    <strong>Durasi Fasilitasi</strong>
                                    <p class="mb-0">{{ $penetapan->durasi_hari }} hari</p>
                                </div>
                            </div>
                        </div>

                        @if ($penetapan->lokasi)
                            <div class="mb-3">
                                <label class="text-muted small">Lokasi Pelaksanaan</label>
                                <p class="mb-0">
                                    <i class='bx bx-map text-danger'></i>
                                    {{ $penetapan->lokasi }}
                                </p>
                            </div>
                        @endif

                        @if ($penetapan->jadwalFasilitasi)
                            <hr>
                            <div class="mb-3">
                                <label class="text-muted small">Referensi Jadwal</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-primary">
                                        Menggunakan Jadwal Fasilitasi #{{ $penetapan->jadwal_fasilitasi_id }}
                                    </span>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informasi Penetapan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Penetapan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Ditetapkan Oleh</label>
                            <p class="mb-0">
                                <i class='bx bx-user'></i> {{ $penetapan->penetap->name ?? '-' }}
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="mb-0">
                                <i class='bx bx-calendar'></i> {{ $penetapan->tanggal_penetapan->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                @if ($penetapan->catatan)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Catatan</h5>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($penetapan->catatan)) !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
