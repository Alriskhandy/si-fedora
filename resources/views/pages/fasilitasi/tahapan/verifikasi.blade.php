@extends('layouts.app')

@section('title', 'Tahapan Verifikasi')

@push('styles')
    <style>
        /* SweetAlert2 z-index fix */
        .swal2-container {
            z-index: 9999 !important;
        }

        .swal2-backdrop-show {
            z-index: 9998 !important;
        }

        .verification-form {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-top: 0.5rem;
            border-radius: 4px;
        }
    </style>
@endpush

@section('main')
    @php
        $isPemohon = auth()->user()->hasRole('pemohon');
        $isVerifikator = auth()->user()->hasRole('verifikator');
        $isAdmin = auth()
            ->user()
            ->hasAnyRole(['admin_peran', 'kaban', 'superadmin']);
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Tahapan Verifikasi - {{ $isVerifikator ? 'Verifikasi Dokumen' : 'Status Verifikasi' }}
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail</a></li>
                        <li class="breadcrumb-item active">Tahapan Verifikasi</li>
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

        @if ($permohonan->status_akhir == 'belum')
            <div class="alert alert-info">
                <i class='bx bx-info-circle me-2'></i>
                @if ($isPemohon)
                    Verifikasi akan dilakukan setelah Anda mengirimkan permohonan.
                @else
                    Permohonan belum disubmit oleh pemohon. Verifikasi belum dapat dilakukan.
                @endif
            </div>

            @if ($isPemohon && $permohonan->jadwalFasilitasi)
                @php
                    $dokumenBelumLengkap = $permohonan->permohonanDokumen->where('is_ada', false)->count() > 0;
                    $batasWaktu = $permohonan->jadwalFasilitasi->batas_permohonan;
                    $batasWaktuTerlewat = $batasWaktu ? now()->gt($batasWaktu) : false;
                @endphp

                @if ($dokumenBelumLengkap && $batasWaktuTerlewat)
                    <div class="alert alert-danger d-flex justify-content-between align-items-center">
                        <div>
                            <i class='bx bx-error-circle me-2'></i>
                            <strong>Batas Waktu Upload Terlewat!</strong><br>
                            <small>Batas upload: {{ \Carbon\Carbon::parse($batasWaktu)->format('d M Y, H:i') }}
                                WIB</small><br>
                            <small class="text-muted">Dokumen yang belum lengkap:
                                <strong>{{ $permohonan->permohonanDokumen->where('is_ada', false)->count() }}</strong></small>
                        </div>
                        <a href="{{ route('perpanjangan-waktu.create', ['permohonan_id' => $permohonan->id]) }}"
                            class="btn btn-warning">
                            <i class='bx bx-time-five me-1'></i>Ajukan Perpanjangan Waktu
                        </a>
                    </div>
                @endif
            @endif
        @elseif ($permohonan->status_akhir == 'proses')
            @if ($isPemohon)
                <div class="alert alert-warning">
                    <i class='bx bx-time-five me-2'></i>
                    <strong>Sedang dalam Proses Verifikasi</strong><br>
                    Permohonan Anda sedang diverifikasi oleh tim verifikator. Mohon menunggu.
                </div>
            @elseif($isVerifikator)
                <div class="alert alert-primary">
                    <i class='bx bx-info-circle me-2'></i>
                    <strong>Permohonan Siap Diverifikasi</strong><br>
                    Silakan lakukan verifikasi dokumen kelengkapan di bawah ini.
                </div>
            @endif
        @elseif($permohonan->status_akhir == 'revisi')
            @if ($isPemohon)
                <div class="alert alert-danger">
                    <i class='bx bx-error-circle me-2'></i>
                    <strong>Dokumen Perlu Revisi</strong><br>
                    Terdapat dokumen yang perlu diperbaiki. Silakan periksa catatan verifikasi di bawah.
                </div>
            @else
                <div class="alert alert-warning">
                    <i class='bx bx-info-circle me-2'></i>
                    Permohonan memerlukan revisi dokumen dari pemohon.
                </div>
            @endif
        @endif

        <!-- Informasi Verifikasi -->
        @if ($permohonan->laporanVerifikasi)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class='bx bx-check-shield me-2'></i>Hasil Verifikasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Status Verifikasi:</th>
                                    <td>
                                        @php
                                            $statusLabel = [
                                                'lengkap' => ['text' => 'Lengkap', 'class' => 'success'],
                                                'tidak_lengkap' => ['text' => 'Tidak Lengkap', 'class' => 'danger'],
                                                'perlu_revisi' => ['text' => 'Perlu Revisi', 'class' => 'warning'],
                                            ];
                                            $current = $statusLabel[
                                                $permohonan->laporanVerifikasi->status_kelengkapan
                                            ] ?? [
                                                'text' => 'Unknown',
                                                'class' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $current['class'] }}">
                                            <i
                                                class='bx bx-{{ $current['class'] == 'success' ? 'check-circle' : 'x-circle' }}'></i>
                                            {{ $current['text'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Diverifikasi Oleh:</th>
                                    <td>{{ $permohonan->laporanVerifikasi->verifikator->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Verifikasi:</th>
                                    <td>{{ $permohonan->laporanVerifikasi->tanggal_verifikasi ? \Carbon\Carbon::parse($permohonan->laporanVerifikasi->tanggal_verifikasi)->format('d F Y') : '-' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="mb-2"><i class='bx bx-note me-1'></i>Catatan Verifikator:</h6>
                                    <p class="mb-0">{{ $permohonan->laporanVerifikasi->catatan ?? 'Tidak ada catatan' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($permohonan->laporanVerifikasi->file_laporan)
                        <div class="mt-3">
                            <a href="{{ asset('storage/' . $permohonan->laporanVerifikasi->file_laporan) }}"
                                target="_blank" class="btn btn-primary">
                                <i class='bx bx-download'></i> Download Laporan Verifikasi
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Detail Verifikasi per Dokumen -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i
                        class='bx bx-list-check me-2'></i>{{ $isVerifikator ? 'Verifikasi Dokumen' : 'Status Verifikasi Dokumen' }}
                </h5>
                @if ($isVerifikator && $permohonan->status_akhir == 'proses')
                    <button type="button" class="btn btn-success" id="submitVerificationBtn">
                        <i class='bx bx-check-circle me-1'></i>Submit Verifikasi
                    </button>
                @endif
            </div>
            <div class="card-body">
                @php
                    $allDokumen = $permohonan->permohonanDokumen;
                @endphp

                @if ($allDokumen->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">Nama Dokumen</th>
                                    <th width="10%">File</th>
                                    <th width="10%">Status Upload</th>
                                    @if ($isVerifikator || $isAdmin)
                                        <th width="15%">Status Verifikasi</th>
                                        <th width="30%">
                                            {{ $isVerifikator && $permohonan->status_akhir == 'proses' ? 'Verifikasi' : 'Catatan' }}
                                        </th>
                                    @else
                                        <th width="15%">Status Verifikasi</th>
                                        <th width="30%">Catatan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allDokumen as $index => $dokumen)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen' }}</strong>
                                            @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->deskripsi)
                                                <br><small
                                                    class="text-muted">{{ $dokumen->masterKelengkapan->deskripsi }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($dokumen->is_ada && $dokumen->file_path)
                                                @php
                                                    $fileExtension = pathinfo($dokumen->file_path, PATHINFO_EXTENSION);
                                                    $isPdf = strtolower($fileExtension) === 'pdf';
                                                @endphp
                                                @if ($isPdf)
                                                    <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                                                        class="btn btn-sm btn-primary" title="Lihat PDF">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                @else
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary btn-preview-excel"
                                                        data-file-url="{{ asset('storage/' . $dokumen->file_path) }}"
                                                        data-file-name="{{ $dokumen->file_name }}" title="Lihat Excel">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($dokumen->is_ada && $dokumen->file_path)
                                                <span class="badge bg-success">
                                                    <i class='bx bx-check'></i> Tersedia
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class='bx bx-x'></i> Belum Upload
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($dokumen->status_verifikasi === 'verified')
                                                <span class="badge bg-success">
                                                    Sesuai
                                                </span>
                                            @elseif($dokumen->status_verifikasi === 'revision')
                                                <span class="badge bg-danger">
                                                    Revisi
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    Belum Diverifikasi
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($isVerifikator && $permohonan->status_akhir == 'proses')
                                                <div class="verification-form">
                                                    <select class="form-select form-select-sm mb-2 dokumen-status"
                                                        data-dokumen-id="{{ $dokumen->id }}">
                                                        <option value="">-- Pilih Status --</option>
                                                        <option value="verified"
                                                            {{ $dokumen->status_verifikasi == 'verified' ? 'selected' : '' }}>
                                                            Sesuai</option>
                                                        <option value="revision"
                                                            {{ $dokumen->status_verifikasi == 'revision' ? 'selected' : '' }}>
                                                            Perlu Revisi</option>
                                                    </select>
                                                    <textarea class="form-control form-control-sm dokumen-catatan" data-dokumen-id="{{ $dokumen->id }}" rows="2"
                                                        placeholder="Catatan verifikasi (opsional)">{{ $dokumen->catatan_verifikasi }}</textarea>
                                                </div>
                                            @else
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
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class='bx bx-folder-open bx-lg mb-2 d-block'></i>
                        Belum ada dokumen yang diupload
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Preview Excel -->
        <div class="modal fade" id="excelPreviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="excelPreviewTitle">
                            <i class="bx bx-file me-2"></i><span id="excelFileName">Preview Excel</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="excelLoadingSpinner" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Memuat file Excel...</p>
                        </div>
                        <div id="excelPreviewContent" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Pilih Sheet:</label>
                                <select id="sheetSelector" class="form-select form-select-sm"
                                    style="width: 250px;"></select>
                            </div>
                            <div id="excelTableContainer" class="table-responsive"
                                style="max-height: 500px; overflow: auto;"></div>
                        </div>
                        <div id="excelErrorMessage" style="display: none;" class="alert alert-danger">
                            <i class="bx bx-error-circle me-2"></i>
                            <span id="errorText"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="downloadExcelBtn" class="btn btn-success" style="display: none;">
                            <i class="bx bx-download me-1"></i>Download File
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SheetJS Library -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <script>
        // Submit verification
        document.getElementById('submitVerificationBtn')?.addEventListener('click', function() {
            const verifications = [];
            let hasEmptyStatus = false;

            document.querySelectorAll('.dokumen-status').forEach(select => {
                const dokumenId = select.dataset.dokumenId;
                const status = select.value;
                const catatan = document.querySelector(`.dokumen-catatan[data-dokumen-id="${dokumenId}"]`)
                    .value;

                if (!status) {
                    hasEmptyStatus = true;
                    return;
                }

                verifications.push({
                    id: dokumenId,
                    status: status,
                    catatan: catatan
                });
            });

            if (hasEmptyStatus) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Verifikasi Belum Lengkap',
                    text: 'Pastikan semua dokumen sudah diverifikasi (pilih status Sesuai atau Perlu Revisi)',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            Swal.fire({
                title: 'Submit Verifikasi?',
                text: 'Pastikan semua verifikasi sudah benar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Submit',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit via AJAX (to be implemented in controller)
                    fetch('{{ route('verifikasi.submit', $permohonan) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                verifications: verifications
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message,
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    window.location.href =
                                        '{{ route('permohonan.show', $permohonan) }}';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message,
                                    confirmButtonColor: '#d33'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan saat memproses verifikasi',
                                confirmButtonColor: '#d33'
                            });
                        });
                }
            });
        });

        // Excel preview functionality
        let currentWorkbook = null;
        let currentFileUrl = null;

        document.querySelectorAll('.btn-preview-excel').forEach(button => {
            button.addEventListener('click', async function() {
                const fileUrl = this.dataset.fileUrl;
                const fileName = this.dataset.fileName;
                currentFileUrl = fileUrl;

                const modal = new bootstrap.Modal(document.getElementById('excelPreviewModal'));
                modal.show();

                document.getElementById('excelFileName').textContent = fileName;
                document.getElementById('excelLoadingSpinner').style.display = 'block';
                document.getElementById('excelPreviewContent').style.display = 'none';
                document.getElementById('excelErrorMessage').style.display = 'none';
                document.getElementById('downloadExcelBtn').style.display = 'none';

                try {
                    const response = await fetch(fileUrl);
                    if (!response.ok) throw new Error('Gagal memuat file');

                    const arrayBuffer = await response.arrayBuffer();
                    const data = new Uint8Array(arrayBuffer);
                    currentWorkbook = XLSX.read(data, {
                        type: 'array'
                    });

                    const sheetSelector = document.getElementById('sheetSelector');
                    sheetSelector.innerHTML = '';
                    currentWorkbook.SheetNames.forEach((sheetName, index) => {
                        const option = document.createElement('option');
                        option.value = index;
                        option.textContent = sheetName;
                        sheetSelector.appendChild(option);
                    });

                    displaySheet(0);
                    document.getElementById('excelLoadingSpinner').style.display = 'none';
                    document.getElementById('excelPreviewContent').style.display = 'block';
                    document.getElementById('downloadExcelBtn').style.display = 'inline-block';
                } catch (error) {
                    document.getElementById('excelLoadingSpinner').style.display = 'none';
                    document.getElementById('excelErrorMessage').style.display = 'block';
                    document.getElementById('errorText').textContent = 'Gagal memuat file Excel: ' +
                        error.message;
                }
            });
        });

        document.getElementById('sheetSelector').addEventListener('change', function() {
            displaySheet(parseInt(this.value));
        });

        function displaySheet(sheetIndex) {
            if (!currentWorkbook) return;
            const sheetName = currentWorkbook.SheetNames[sheetIndex];
            const worksheet = currentWorkbook.Sheets[sheetName];
            const html = XLSX.utils.sheet_to_html(worksheet, {
                header: '',
                footer: ''
            });
            const styledHtml = html.replace('<table>',
                '<table class="table table-bordered table-striped table-hover table-sm">');
            document.getElementById('excelTableContainer').innerHTML = styledHtml;
        }

        document.getElementById('downloadExcelBtn').addEventListener('click', function() {
            if (currentFileUrl) window.location.href = currentFileUrl;
        });
    </script>
@endpush
