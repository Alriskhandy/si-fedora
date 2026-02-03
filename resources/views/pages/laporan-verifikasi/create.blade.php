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
                        <li class="breadcrumb-item"><a href="{{ route('laporan-verifikasi.index') }}">Laporan Verifikasi</a>
                        </li>
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
                        <form action="{{ route('laporan-verifikasi.store', $permohonan) }}" method="POST" id="laporanForm">
                            @csrf
                            <input type="hidden" name="status_kelengkapan" id="status_kelengkapan" value="">

                            <!-- Ringkasan Verifikasi -->
                            <div class="mb-4">
                                <label for="ringkasan_verifikasi" class="form-label">
                                    Ringkasan Hasil Verifikasi <span class="text-danger">*</span>
                                </label>
                                <textarea name="ringkasan_verifikasi" id="ringkasan_verifikasi"
                                    class="form-control @error('ringkasan_verifikasi') is-invalid @enderror" rows="5" required>{{ old('ringkasan_verifikasi') }}</textarea>
                                @error('ringkasan_verifikasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Catatan Admin -->
                            <div class="mb-4">
                                <label for="catatan_admin" class="form-label">
                                    Catatan Tambahan
                                </label>
                                <textarea name="catatan_admin" id="catatan_admin" class="form-control @error('catatan_admin') is-invalid @enderror"
                                    rows="3">{{ old('catatan_admin') }}</textarea>
                                @error('catatan_admin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <a href="{{ route('laporan-verifikasi.index') }}" class="btn btn-outline-secondary">
                                    <i class='bx bx-x'></i> Batal
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning" onclick="setStatus('tidak_lengkap')">
                                        <i class='bx bx-error-circle'></i> Simpan - Tidak Lengkap
                                    </button>
                                    <button type="submit" class="btn btn-success" onclick="setStatus('lengkap')">
                                        <i class='bx bx-check-circle'></i> Simpan - Lengkap
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function setStatus(status) {
                document.getElementById('status_kelengkapan').value = status;
            }
        </script>
    @endpush
@endsection
