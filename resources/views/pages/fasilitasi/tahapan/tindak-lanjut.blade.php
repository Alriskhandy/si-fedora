@extends('layouts.app')

@section('title', 'Tahapan Tindak Lanjut Hasil Fasilitasi / Evaluasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Tindak Lanjut Hasil Fasilitasi / Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail</a></li>
                        <li class="breadcrumb-item active">Tahapan Tindak Lanjut</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('permohonan.show', $permohonan) }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class='bx bx-check-circle me-2'></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class='bx bx-error-circle me-2'></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class='bx bx-error-circle me-2'></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Single Card: Tindak Lanjut Hasil Fasilitasi -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                <h5 class="fw-bold mb-1">
                    <i class='bx bx-task me-2'></i>Tindak Lanjut Hasil Fasilitasi / Evaluasi
                </h5>
            </div>
            <div class="card-body">
                @if($dokumenTindakLanjut && !$dokumenTindakLanjut->verified_by && auth()->user()->hasRole('pemohon'))
                    <!-- Sudah Upload tapi Belum Submit (Pemohon) -->
                    <div class="alert alert-warning mb-4">
                        <div class="d-flex align-items-center">
                            <i class='bx bx-error-circle me-2' style="font-size: 24px;"></i>
                            <div>
                                <strong>Dokumen Belum Disubmit</strong>
                                <p class="mb-0 small">Dokumen sudah diupload namun belum disubmit. Silakan preview dan
                                    submit dokumen.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-0">
                        <!-- Kolom Kiri: Informasi -->
                        <div class="col-md-4 border-end">
                            <div class="p-4">
                                <h6 class="mb-4 text-uppercase text-muted small fw-bold">
                                    <i class='bx bx-info-circle me-1'></i>Informasi Tindak Lanjut
                                </h6>

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Kabupaten/Kota</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-map text-primary me-1'></i>
                                        {{ $permohonan->kabupatenKota->nama ?? '-' }}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Jenis Dokumen</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-file text-info me-1'></i>
                                        {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                    </div>
                                </div>

                                @if ($permohonan->tindakLanjut && $permohonan->tindakLanjut->jenis_tindak_lanjut)
                                    <div class="mb-4">
                                        <label class="text-muted small d-block mb-2">Jenis Tindak Lanjut</label>
                                        @php
                                            $jenisLabels = [
                                                'perbaikan' => ['text' => 'Perbaikan', 'class' => 'warning'],
                                                'revisi_total' => ['text' => 'Revisi Total', 'class' => 'danger'],
                                                'sudah_sesuai' => ['text' => 'Sudah Sesuai', 'class' => 'success'],
                                            ];
                                            $current = $jenisLabels[$permohonan->tindakLanjut->jenis_tindak_lanjut] ?? [
                                                'text' => 'Unknown',
                                                'class' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $current['class'] }} px-3 py-2">
                                            {{ $current['text'] }}
                                        </span>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Tanggal Upload</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-calendar-check text-success me-1'></i>
                                        {{ $dokumenTindakLanjut->created_at ? $dokumenTindakLanjut->created_at->format('d F Y, H:i') : '-' }}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Status</label>
                                    <span class="badge bg-warning px-3 py-2">
                                        <i class='bx bx-time me-1'></i>Belum Disubmit
                                    </span>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                        data-bs-target="#uploadUlangModal">
                                        <i class='bx bx-refresh me-1'></i>Upload Ulang
                                    </button>
                                    <button type="button" class="btn btn-primary" id="btnSubmitDokumen">
                                        <i class='bx bx-check-circle me-1'></i>Submit Dokumen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Preview PDF -->
                        <div class="col-md-8">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 fw-bold">
                                        <i class='bx bx-file-pdf me-1 text-danger'></i>Preview Dokumen
                                    </h6>
                                    <a href="{{ asset('storage/' . $dokumenTindakLanjut->file_path) }}"
                                        class="btn btn-primary btn-sm shadow-sm" target="_blank">
                                        <i class='bx bx-download me-1'></i>Download PDF
                                    </a>
                                </div>

                                <div class="ratio ratio-16x9 border rounded" style="min-height: 600px;">
                                    <iframe
                                        src="{{ asset('storage/' . $dokumenTindakLanjut->file_path) }}#toolbar=1&view=FitH"
                                        type="application/pdf" width="100%" height="600px" style="border: none;">
                                        <p class="text-center py-5">
                                            Browser Anda tidak mendukung preview PDF.
                                            <a href="{{ asset('storage/' . $dokumenTindakLanjut->file_path) }}"
                                                class="btn btn-primary" target="_blank">
                                                <i class='bx bx-download me-1'></i>Download PDF
                                            </a>
                                        </p>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($dokumenTindakLanjut && $dokumenTindakLanjut->verified_by && $dokumenTindakLanjut->verified_at)
                    <!-- Sudah Ada Dokumen - 2 Kolom (4:8) -->
                    <div class="row g-0">
                        <!-- Kolom Kiri: Informasi -->
                        <div class="col-md-4 border-end">
                            <div class="p-4">
                                <h6 class="mb-4 text-uppercase text-muted small fw-bold">
                                    <i class='bx bx-info-circle me-1'></i>Informasi Tindak Lanjut
                                </h6>

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Kabupaten/Kota</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-map text-primary me-1'></i>
                                        {{ $permohonan->kabupatenKota->nama ?? '-' }}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Jenis Dokumen</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-file text-info me-1'></i>
                                        {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                    </div>
                                </div>

                                @if ($permohonan->tindakLanjut && $permohonan->tindakLanjut->jenis_tindak_lanjut)
                                    <div class="mb-4">
                                        <label class="text-muted small d-block mb-2">Jenis Tindak Lanjut</label>
                                        @php
                                            $jenisLabels = [
                                                'perbaikan' => ['text' => 'Perbaikan', 'class' => 'warning'],
                                                'revisi_total' => ['text' => 'Revisi Total', 'class' => 'danger'],
                                                'sudah_sesuai' => ['text' => 'Sudah Sesuai', 'class' => 'success'],
                                            ];
                                            $current = $jenisLabels[$permohonan->tindakLanjut->jenis_tindak_lanjut] ?? [
                                                'text' => 'Unknown',
                                                'class' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $current['class'] }} px-3 py-2">
                                            {{ $current['text'] }}
                                        </span>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Tanggal Submit</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-calendar-check text-success me-1'></i>
                                        {{ $dokumenTindakLanjut->updated_at ? $dokumenTindakLanjut->updated_at->format('d F Y, H:i') : '-' }}
                                    </div>
                                </div>

                                <div>
                                    <label class="text-muted small d-block mb-2">Status</label>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class='bx bx-check-shield me-1'></i>Sudah Disubmit
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Preview PDF -->
                        <div class="col-md-8">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 fw-bold">
                                        <i class='bx bx-file-pdf me-1 text-danger'></i>Preview Dokumen
                                    </h6>
                                    <a href="{{ asset('storage/' . $dokumenTindakLanjut->file_path) }}"
                                        class="btn btn-primary btn-sm shadow-sm" target="_blank">
                                        <i class='bx bx-download me-1'></i>Download PDF
                                    </a>
                                </div>

                                <div class="ratio ratio-16x9 border rounded" style="min-height: 600px;">
                                    <iframe
                                        src="{{ asset('storage/' . $dokumenTindakLanjut->file_path) }}#toolbar=1&view=FitH"
                                        type="application/pdf" width="100%" height="600px" style="border: none;">
                                        <p class="text-center py-5">
                                            Browser Anda tidak mendukung preview PDF.
                                            <a href="{{ asset('storage/' . $dokumenTindakLanjut->file_path) }}"
                                                class="btn btn-primary" target="_blank">
                                                <i class='bx bx-download me-1'></i>Download PDF
                                            </a>
                                        </p>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Belum Ada Dokumen -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class='bx bx-file-blank' style="font-size: 80px; color: #6c757d;"></i>
                        </div>
                        <h5 class="mb-3">Dokumen Belum Tersedia</h5>

                        @if (auth()->user()->hasRole('pemohon'))
                            @if (!$permohonan->tindakLanjut)
                                <p class="text-muted mb-4">
                                    Form upload dokumen perencanaan yang telah diperbaiki akan tersedia setelah<br>
                                    hasil fasilitasi divalidasi oleh admin.
                                </p>
                            @else
                                <p class="text-muted mb-4">
                                    Silakan upload dokumen perencanaan yang telah di tindak lanjut<br>
                                    berdasarkan hasil fasilitasi / evaluasi.
                                </p>
                                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#uploadDokumenModal">
                                    <i class='bx bx-upload me-2'></i>Upload Dokumen
                                </button>
                            @endif
                        @else
                            {{-- Untuk role selain pemohon --}}
                            @if (!$permohonan->tindakLanjut)
                                <p class="text-muted mb-4">
                                    Dokumen tindak lanjut hasil fasilitasi / evaluasi belum tersedia. <br>
                                    Silahkan kembali lagi nanti.
                                </p>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Upload Dokumen Pertama Kali (untuk Pemohon) -->
        @if (auth()->user()->hasRole('pemohon') && !$dokumenTindakLanjut)
            <div class="modal fade" id="uploadDokumenModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary bg-opacity-10 border-bottom-0">
                            <div>
                                <h5 class="modal-title fw-bold">
                                    <i class='bx bx-upload me-2 text-primary'></i>Upload Dokumen Tindak Lanjut
                                </h5>
                                <p class="mb-0 small text-muted">Upload dokumen perencanaan yang telah diperbaiki</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="{{ route('tindak-lanjut.upload', $permohonan) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body p-4">
                                @if ($permohonan->tindakLanjut)
                                    <div class="alert alert-info border-0 shadow-sm mb-4">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <i class='bx bx-info-circle' style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong>Informasi Tindak Lanjut:</strong>
                                                <ul class="mb-0 mt-2">
                                                    <li>Jenis:
                                                        <strong>{{ $permohonan->tindakLanjut->jenis_tindak_lanjut === 'perbaikan' ? 'Perbaikan' : ($permohonan->tindakLanjut->jenis_tindak_lanjut === 'revisi_total' ? 'Revisi Total' : 'Lainnya') }}</strong>
                                                    </li>
                                                    @if ($permohonan->tindakLanjut->batas_waktu)
                                                        <li>Batas Waktu:
                                                            <strong>{{ \Carbon\Carbon::parse($permohonan->tindakLanjut->batas_waktu)->format('d F Y') }}</strong>
                                                            @if (now()->gt($permohonan->tindakLanjut->batas_waktu))
                                                                <span class="badge bg-danger ms-2">Sudah Lewat</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-success ms-2">{{ now()->diffInDays($permohonan->tindakLanjut->batas_waktu) }}
                                                                    hari lagi</span>
                                                            @endif
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class='bx bx-file-pdf me-1'></i>
                                        File Dokumen <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" name="file" class="form-control form-control-lg"
                                        accept=".pdf" required>
                                    <div class="form-text">
                                        <i class='bx bx-info-circle me-1'></i>
                                        Format: PDF | Maksimal: 100MB
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 bg-light">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class='bx bx-x me-1'></i>Batal
                                </button>
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class='bx bx-upload me-1'></i>Upload Dokumen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Upload Ulang (untuk Pemohon) -->
        @if ($dokumenTindakLanjut && !$dokumenTindakLanjut->verified_by && auth()->user()->hasRole('pemohon'))
            <div class="modal fade" id="uploadUlangModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-warning bg-opacity-10 border-bottom-0">
                            <div>
                                <h5 class="modal-title fw-bold">
                                    <i class='bx bx-refresh me-2 text-warning'></i>Upload Ulang Dokumen Tindak Lanjut
                                </h5>
                                <p class="mb-0 small text-muted">Dokumen lama akan digantikan dengan dokumen baru</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="{{ route('tindak-lanjut.upload', $permohonan) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="alert alert-warning border-0 shadow-sm mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class='bx bx-error-circle' style="font-size: 24px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>Perhatian:</strong> Dokumen yang sudah ada akan diganti dengan dokumen
                                            baru yang Anda upload.
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class='bx bx-file-pdf me-1'></i>
                                        File Dokumen Baru <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" name="file" class="form-control form-control-lg"
                                        accept=".pdf" required>
                                    <div class="form-text">
                                        <i class='bx bx-info-circle me-1'></i>
                                        Format: PDF | Maksimal: 100MB
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 bg-light">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class='bx bx-x me-1'></i>Batal
                                </button>
                                <button type="submit" class="btn btn-warning shadow-sm">
                                    <i class='bx bx-refresh me-1'></i>Upload Ulang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnSubmit = document.getElementById('btnSubmitDokumen');
            
            if (btnSubmit) {
                btnSubmit.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Check if Swal is available
                    if (typeof Swal === 'undefined') {
                        if (confirm('Apakah Anda yakin ingin submit dokumen ini?')) {
                            submitForm();
                        }
                        return;
                    }
                    
                    Swal.fire({
                        title: 'Konfirmasi Submit Dokumen',
                        html: '<p class="mb-2">Setelah disubmit, dokumen akan dapat dilihat oleh admin dan tim lainnya.</p><p class="mb-0"><strong>Apakah Anda yakin ingin submit dokumen ini?</strong></p>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bx bx-check-circle me-1"></i> Ya, Submit',
                        cancelButtonText: '<i class="bx bx-x me-1"></i> Tidak, Batal',
                        customClass: {
                            confirmButton: 'btn btn-primary shadow-sm',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitForm();
                        }
                    });
                });
            }
            
            function submitForm() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('tindak-lanjut.submit', $permohonan) }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
@endpush
