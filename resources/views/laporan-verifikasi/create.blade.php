@extends('layouts.app')

@section('title', 'Buat Laporan Verifikasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Buat Laporan Hasil Verifikasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('laporan-verifikasi.index') }}">Laporan Verifikasi</a></li>
                        <li class="breadcrumb-item active">Buat Laporan</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('laporan-verifikasi.index') }}" class="btn btn-secondary">
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
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-success">
                                    <i class='bx bx-check-circle'></i> Selesai Diverifikasi
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Total Dokumen</span>
                            <h4 class="mb-0">{{ $dokumenStats->total ?? 0 }}</h4>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-success">
                                <i class='bx bx-check-circle'></i> Terverifikasi
                            </span>
                            <h5 class="mb-0 text-success">{{ $dokumenStats->verified ?? 0 }}</h5>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-warning">
                                <i class='bx bx-error-circle'></i> Perlu Revisi
                            </span>
                            <h5 class="mb-0 text-warning">{{ $dokumenStats->revision ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Laporan -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Form Laporan Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('laporan-verifikasi.store', $permohonan) }}" method="POST">
                            @csrf

                            <!-- Ringkasan Verifikasi -->
                            <div class="mb-4">
                                <label for="ringkasan_verifikasi" class="form-label">
                                    Ringkasan Hasil Verifikasi <span class="text-danger">*</span>
                                </label>
                                <textarea name="ringkasan_verifikasi" id="ringkasan_verifikasi" 
                                          class="form-control @error('ringkasan_verifikasi') is-invalid @enderror" 
                                          rows="6" required>{{ old('ringkasan_verifikasi') }}</textarea>
                                <small class="text-muted">
                                    Jelaskan hasil verifikasi dokumen secara keseluruhan
                                </small>
                                @error('ringkasan_verifikasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Kelengkapan -->
                            <div class="mb-4">
                                <label class="form-label">
                                    Status Kelengkapan Dokumen <span class="text-danger">*</span>
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 {{ old('status_kelengkapan') == 'lengkap' ? 'border-success' : '' }}">
                                            <input class="form-check-input" type="radio" 
                                                   name="status_kelengkapan" id="lengkap" 
                                                   value="lengkap" 
                                                   {{ old('status_kelengkapan') == 'lengkap' ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label w-100" for="lengkap">
                                                <div class="d-flex align-items-center">
                                                    <i class='bx bx-check-circle text-success fs-4 me-2'></i>
                                                    <div>
                                                        <strong>Lengkap</strong>
                                                        <p class="mb-0 small text-muted">
                                                            Semua dokumen telah terverifikasi dengan baik
                                                        </p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 {{ old('status_kelengkapan') == 'tidak_lengkap' ? 'border-warning' : '' }}">
                                            <input class="form-check-input" type="radio" 
                                                   name="status_kelengkapan" id="tidak_lengkap" 
                                                   value="tidak_lengkap"
                                                   {{ old('status_kelengkapan') == 'tidak_lengkap' ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="tidak_lengkap">
                                                <div class="d-flex align-items-center">
                                                    <i class='bx bx-error-circle text-warning fs-4 me-2'></i>
                                                    <div>
                                                        <strong>Tidak Lengkap</strong>
                                                        <p class="mb-0 small text-muted">
                                                            Ada dokumen yang perlu diperbaiki
                                                        </p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('status_kelengkapan')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Catatan Admin -->
                            <div class="mb-4">
                                <label for="catatan_admin" class="form-label">
                                    Catatan Tambahan (Opsional)
                                </label>
                                <textarea name="catatan_admin" id="catatan_admin" 
                                          class="form-control @error('catatan_admin') is-invalid @enderror" 
                                          rows="4">{{ old('catatan_admin') }}</textarea>
                                @error('catatan_admin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Informasi Statistik (Read-only) -->
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class='bx bx-info-circle'></i> Informasi Statistik
                                </h6>
                                <p class="mb-0">
                                    Statistik dokumen akan otomatis tersimpan dalam laporan: <br>
                                    <strong>{{ $dokumenStats->verified ?? 0 }}</strong> dokumen terverifikasi, 
                                    <strong>{{ $dokumenStats->revision ?? 0 }}</strong> perlu revisi dari total 
                                    <strong>{{ $dokumenStats->total ?? 0 }}</strong> dokumen.
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('laporan-verifikasi.index') }}" class="btn btn-secondary">
                                    <i class='bx bx-x'></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class='bx bx-save'></i> Simpan Laporan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
