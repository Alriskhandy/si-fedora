@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Undangan</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('pemohon') ? route('pemohon.undangan.index') : route('my-undangan.index') }}">Undangan Saya</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ auth()->user()->hasRole('pemohon') ? route('pemohon.undangan.index') : route('my-undangan.index') }}"
                class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <!-- Informasi Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kabupaten/Kota</dt>
                            <dd class="col-sm-7">{{ $undanganPenerima->undangan->permohonan->kabupatenKota->nama }}</dd>

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $undanganPenerima->undangan->permohonan->no_permohonan }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Status Undangan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Jenis Penerima</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-label-primary">
                                    {{ ucfirst($undanganPenerima->jenis_penerima) }}
                                </span>
                            </dd>

                            <dt class="col-sm-5">Status Baca</dt>
                            <dd class="col-sm-7">
                                @if ($undanganPenerima->dibaca)
                                    <span class="badge bg-success">Sudah Dibaca</span>
                                @else
                                    <span class="badge bg-secondary">Belum Dibaca</span>
                                @endif
                            </dd>

                            @if ($undanganPenerima->tanggal_dibaca)
                                <dt class="col-sm-5">Tanggal Dibaca</dt>
                                <dd class="col-sm-7">{{ $undanganPenerima->tanggal_dibaca->format('d M Y H:i') }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- File Undangan -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">File Undangan</h5>
                        @if ($undanganPenerima->undangan->file_undangan)
                            <a href="{{ route('undangan-pelaksanaan.download', $undanganPenerima->undangan->permohonan) }}"
                                class="btn btn-sm btn-success">
                                <i class="bx bx-download"></i> Download PDF
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($undanganPenerima->undangan->tanggal_dikirim)
                            <dl class="row mb-3">
                                <dt class="col-sm-3">Tanggal Dikirim</dt>
                                <dd class="col-sm-9">
                                    {{ $undanganPenerima->undangan->tanggal_dikirim->format('d F Y H:i') }}
                                </dd>
                            </dl>
                        @endif

                        @if ($undanganPenerima->undangan->file_undangan)
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-file-blank"></i>
                                <strong>File undangan tersedia</strong>
                                <p class="mb-0 mt-2">Klik tombol "Download PDF" di atas untuk mengunduh file undangan
                                    lengkap.</p>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bx bx-error-circle"></i> File undangan belum tersedia
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Jadwal Fasilitasi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Jadwal Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Tanggal</dt>
                            <dd class="col-sm-9">
                                {{ $undanganPenerima->undangan->penetapanJadwal->tanggal_mulai->format('d F Y') }} -
                                {{ $undanganPenerima->undangan->penetapanJadwal->tanggal_selesai->format('d F Y') }}
                            </dd>

                            <dt class="col-sm-3">Lokasi</dt>
                            <dd class="col-sm-9">{{ $undanganPenerima->undangan->penetapanJadwal->lokasi ?? '-' }}</dd>

                            <dt class="col-sm-3">Durasi</dt>
                            <dd class="col-sm-9">{{ $undanganPenerima->undangan->penetapanJadwal->durasi_hari }} hari
                            </dd>

                            @if ($undanganPenerima->undangan->penetapanJadwal->catatan)
                                <dt class="col-sm-3">Catatan</dt>
                                <dd class="col-sm-9">
                                    {{ $undanganPenerima->undangan->penetapanJadwal->catatan }}
                                </dd>
                            @endif
                        </dl>

                        <div class="alert alert-info mt-3 mb-0">
                            <h6 class="alert-heading mb-2">
                                <i class="bx bx-info-circle"></i> Informasi Penting
                            </h6>
                            <p class="mb-0">Harap mempersiapkan segala sesuatu yang diperlukan untuk pelaksanaan
                                fasilitasi sesuai jadwal yang telah ditetapkan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
