@extends('layouts.app')

@section('title', 'Detail Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Jadwal Fasilitasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pemohon.jadwal.index') }}">Jadwal Fasilitasi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('pemohon.jadwal.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Jadwal</h5>
                        <span
                            class="badge bg-label-{{ $jadwal->status == 'published' ? 'success' : ($jadwal->status == 'closed' ? 'danger' : 'secondary') }}">
                            {{ $jadwal->status_label }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <label class="text-muted">Jenis Dokumen</label>
                            </div>
                            <div class="col-sm-8">
                                <strong>{{ $jadwal->jenis_dokumen_label }}</strong>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <label class="text-muted">Tahun Anggaran</label>
                            </div>
                            <div class="col-sm-8">
                                <strong>{{ $jadwal->tahun_anggaran }}</strong>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <label class="text-muted">Batas Permohonan</label>
                            </div>
                            <div class="col-sm-8">
                                @if ($jadwal->batas_permohonan)
                                    <strong
                                        class="text-{{ $jadwal->batas_permohonan > now()->addDays(7) ? 'success' : 'warning' }}">
                                        {{ $jadwal->batas_permohonan->format('d F Y') }}
                                    </strong>
                                    @if ($jadwal->batas_permohonan > now())
                                        <small class="d-block text-muted">
                                            {{ $jadwal->batas_permohonan->diffForHumans() }}
                                        </small>
                                    @else
                                        <small class="d-block text-danger">Sudah berakhir</small>
                                    @endif
                                @else
                                    <span class="text-muted">Tidak ada batas</span>
                                @endif
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <label class="text-muted">Periode Fasilitasi</label>
                            </div>
                            <div class="col-sm-8">
                                <strong>{{ $jadwal->tanggal_mulai->format('d F Y') }}</strong> sampai
                                <strong>{{ $jadwal->tanggal_selesai->format('d F Y') }}</strong>
                            </div>
                        </div>
                        @if ($jadwal->undangan_file)
                            <hr class="my-3">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="text-muted">File Undangan</label>
                                </div>
                                <div class="col-sm-8">
                                    <a href="{{ url('storage/' . $jadwal->undangan_file) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary me-2">
                                        <i class='bx bx-show me-1'></i> Lihat
                                    </a>
                                    <a href="{{ url('storage/' . $jadwal->undangan_file) }}" download
                                        class="btn btn-sm btn-outline-success">
                                        <i class='bx bx-download me-1'></i> Download
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Action Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Aksi</h5>
                    </div>
                    <div class="card-body">
                        @if ($existingPermohonan)
                            <div class="alert alert-info mb-3">
                                <i class='bx bx-info-circle me-1'></i>
                                Anda sudah membuat permohonan untuk jadwal ini
                            </div>
                            <a href="{{ route('permohonan.show', $existingPermohonan->id) }}"
                                class="btn btn-primary w-100 mb-2">
                                <i class='bx bx-show me-1'></i> Lihat Permohonan
                            </a>
                        @else
                            @if ($jadwal->batas_permohonan > now())
                                <div class="alert alert-success mb-3">
                                    <i class='bx bx-check-circle me-1'></i>
                                    Jadwal masih aktif, Anda dapat membuat permohonan
                                </div>
                                <a href="{{ route('permohonan.create', ['jadwal_id' => $jadwal->id]) }}"
                                    class="btn btn-primary w-100 mb-2">
                                    <i class='bx bx-plus me-1'></i> Buat Permohonan Baru
                                </a>
                            @else
                                <div class="alert alert-warning mb-3">
                                    <i class='bx bx-time me-1'></i>
                                    Jadwal sudah berakhir
                                </div>
                                <button class="btn btn-secondary w-100 mb-2" disabled>
                                    <i class='bx bx-x-circle me-1'></i> Tidak Dapat Membuat Permohonan
                                </button>
                            @endif
                        @endif

                        <a href="{{ route('pemohon.jadwal.index') }}" class="btn btn-outline-secondary w-100">
                            <i class='bx bx-arrow-back me-1'></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Penting</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class='bx bx-check text-success me-2'></i>
                                <small>Pastikan dokumen persyaratan sudah disiapkan</small>
                            </li>
                            <li class="mb-2">
                                <i class='bx bx-check text-success me-2'></i>
                                <small>Permohonan harus dikirim sebelum batas waktu</small>
                            </li>
                            <li class="mb-2">
                                <i class='bx bx-check text-success me-2'></i>
                                <small>Dokumen akan diverifikasi oleh tim</small>
                            </li>
                            <li class="mb-0">
                                <i class='bx bx-check text-success me-2'></i>
                                <small>Anda akan menerima notifikasi hasil verifikasi</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
