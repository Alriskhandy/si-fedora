@extends('layouts.app')

@section('title', 'Tahapan Permohonan - Upload Dokumen')

@push('styles')
<style>
    /* SweetAlert2 z-index fix */
    .swal2-container {
        z-index: 9999 !important;
    }
    .swal2-backdrop-show {
        z-index: 9998 !important;
    }
</style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Tahapan Permohonan - Upload Dokumen
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail</a></li>
                        <li class="breadcrumb-item active">Tahapan Permohonan</li>
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

        <!-- Info Permohonan -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Kabupaten/Kota</small>
                        <p class="mb-0 fw-semibold">{{ $permohonan->kabupatenKota->nama ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Jenis Dokumen</small>
                        <p class="mb-0"><span class="badge bg-primary">{{ $permohonan->jenisDokumen->nama ?? '-' }}</span>
                        </p>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">Tahun</small>
                        <p class="mb-0 fw-semibold">{{ $permohonan->tahun }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Status</small>
                        <p class="mb-0"><span
                                class="badge bg-{{ $permohonan->statusBadgeClass }}">{{ strtoupper($permohonan->status_akhir) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @php
            $totalDokumen = $permohonan->permohonanDokumen->count();
            $dokumenLengkap = $permohonan->permohonanDokumen->where('is_ada', true)->count();
            $progress = $totalDokumen > 0 ? ($dokumenLengkap / $totalDokumen) * 100 : 0;
            $isPemohon = auth()->user()->hasRole('pemohon');
        @endphp

        <!-- Dokumen Kelengkapan -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <h5 class="mb-1"><i class='bx bx-file me-2'></i>Dokumen Kelengkapan Fasilitasi & Evaluasi</h5>
                    <small class="text-muted">
                        Progress:
                        <strong class="{{ $progress == 100 ? 'text-success' : 'text-warning' }}">
                            {{ $dokumenLengkap }}/{{ $totalDokumen }}
                        </strong>
                        dokumen lengkap ({{ number_format($progress, 0) }}%)
                    </small>
                </div>

                @if ($isPemohon && $permohonan->status_akhir == 'belum' && $progress == 100)
                    <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST" id="submitPermohonanForm">
                        @csrf
                        <button type="submit" class="btn btn-success" id="submitPermohonanBtn">
                            <i class='bx bx-check-circle me-1'></i>Submit Dokumen
                        </button>
                    </form>
                @elseif($permohonan->status_akhir != 'belum')
                    <span class="badge bg-success">
                        <i class='bx bx-check-circle'></i> Dokumen Telah Disubmit
                    </span>
                @endif
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-{{ $progress == 100 ? 'success' : 'warning' }}" role="progressbar"
                        style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>

                @if ($isPemohon && $permohonan->status_akhir == 'belum' && $progress < 100)
                    <div class="alert alert-info mb-4">
                        <i class='bx bx-info-circle me-2'></i>
                        Silakan upload semua dokumen kelengkapan. Setelah semua dokumen terupload, Anda dapat submit dokumen
                        untuk diverifikasi.
                    </div>
                @elseif(!$isPemohon)
                    <div class="alert alert-secondary mb-4">
                        <i class='bx bx-info-circle me-2'></i>
                        Berikut adalah daftar dokumen kelengkapan yang telah diupload oleh pemohon.
                    </div>
                @endif

                <!-- Tabel Dokumen -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="40%">Nama Dokumen</th>
                                <th width="10%">File</th>
                                <th width="20%" class="text-center">Status Upload</th>
                                @if ($isPemohon && $permohonan->status_akhir == 'belum')
                                    <th width="25%" class="text-center">Aksi</th>
                                @else
                                    <th width="25%">Keterangan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permohonan->permohonanDokumen->sortBy('masterKelengkapan.urutan') as $index => $dokumen)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? '-' }}</strong>
                                        @if ($dokumen->masterKelengkapan->deskripsi)
                                            <br><small
                                                class="text-muted">{{ $dokumen->masterKelengkapan->deskripsi }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dokumen->file_path)
                                            @php
                                                $fileExtension = pathinfo($dokumen->file_path, PATHINFO_EXTENSION);
                                                $isPdf = strtolower($fileExtension) === 'pdf';
                                            @endphp
                                            @if ($isPdf)
                                                <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                                                    class="btn btn-icon btn-primary" title="Lihat Dokumen PDF">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-icon btn-primary btn-preview-excel"
                                                    data-file-url="{{ asset('storage/' . $dokumen->file_path) }}"
                                                    data-file-name="{{ $dokumen->file_name }}" title="Lihat Excel">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($dokumen->is_ada)
                                            <span class="badge bg-label-success">
                                                <i class='bx bx-check'></i> Tersedia
                                            </span>
                                        @else
                                            <span class="badge bg-label-warning">
                                                <i class='bx bx-x'></i> Belum Upload
                                            </span>
                                        @endif
                                    </td>
                                    <td
                                        class="{{ $isPemohon && $permohonan->status_akhir == 'belum' ? 'text-center' : '' }}">
                                        @if ($isPemohon && $permohonan->status_akhir == 'belum')
                                            @if (!$dokumen->is_ada)
                                                <form action="{{ route('permohonan-dokumen.upload', $dokumen) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    class="upload-dokumen-form" data-dokumen-id="{{ $dokumen->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="file" name="file" class="d-none file-input"
                                                        accept=".pdf,.xlsx,.xls" required>
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary btn-upload-trigger">
                                                        <i class="bx bx-upload"></i> Upload
                                                    </button>
                                                    <small class="d-block mt-1 text-muted">PDF atau Excel (Max
                                                        100MB)</small>
                                                </form>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class='bx bx-check'></i> Selesai
                                                </span>
                                            @endif
                                        @else
                                            @if ($dokumen->is_ada)
                                                <small class="text-muted">
                                                    <i class='bx bx-check-circle text-success'></i>
                                                    Diupload {{ $dokumen->updated_at->diffForHumans() }}
                                                </small>
                                            @else
                                                <small class="text-muted">
                                                    <i class='bx bx-x-circle text-warning'></i>
                                                    Belum tersedia
                                                </small>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class='bx bx-folder-open bx-lg text-muted mb-2 d-block'></i>
                                        <p class="text-muted mb-0">Belum ada dokumen kelengkapan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
                            <select id="sheetSelector" class="form-select form-select-sm" style="width: 250px;">
                            </select>
                        </div>
                        <div id="excelTableContainer" class="table-responsive"
                            style="max-height: 500px; overflow: auto;">
                        </div>
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
@endsection

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SheetJS Library -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <script>
        // Handle file upload button trigger
        document.querySelectorAll('.btn-upload-trigger').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.upload-dokumen-form');
                const fileInput = form.querySelector('.file-input');
                fileInput.click();
            });
        });

        // Auto submit on file selection with validation
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const maxSize = 100 * 1024 * 1024; // 100MB in bytes
                    const allowedTypes = [
                        'application/pdf',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ];

                    // Validate file size
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Terlalu Besar',
                            text: 'Ukuran file maksimal adalah 100MB',
                            confirmButtonColor: '#d33'
                        });
                        this.value = ''; // Reset input
                        return;
                    }

                    // Validate file type
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format File Tidak Didukung',
                            text: 'File harus berformat PDF atau Excel (xlsx, xls)',
                            confirmButtonColor: '#d33'
                        });
                        this.value = ''; // Reset input
                        return;
                    }

                    const form = this.closest('.upload-dokumen-form');
                    const button = form.querySelector('.btn-upload-trigger');

                    // Show loading state
                    button.disabled = true;
                    button.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';

                    // Submit form
                    form.submit();
                }
            });
        });

        // Handle submit permohonan
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submitPermohonanBtn');
            const submitForm = document.getElementById('submitPermohonanForm');

            if (submitBtn && submitForm) {
                submitBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Submit Dokumen?',
                            text: 'Pastikan semua dokumen sudah lengkap dan benar. Setelah disubmit, dokumen akan diverifikasi.',
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
                                // Submit form
                                submitForm.submit();
                            }
                        });
                    } else {
                        // Fallback jika SweetAlert tidak ada
                        if (confirm('Submit dokumen? Pastikan semua dokumen sudah lengkap dan benar.')) {
                            submitForm.submit();
                        }
                    }
                });
            }
        });

        // ============================================
        // EXCEL PREVIEW FUNCTIONALITY
        // ============================================
        let currentWorkbook = null;
        let currentFileUrl = null;

        // Handle Excel preview button click
        document.querySelectorAll('.btn-preview-excel').forEach(button => {
            button.addEventListener('click', async function() {
                const fileUrl = this.dataset.fileUrl;
                const fileName = this.dataset.fileName;

                currentFileUrl = fileUrl;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('excelPreviewModal'));
                modal.show();

                // Reset modal content
                document.getElementById('excelFileName').textContent = fileName;
                document.getElementById('excelLoadingSpinner').style.display = 'block';
                document.getElementById('excelPreviewContent').style.display = 'none';
                document.getElementById('excelErrorMessage').style.display = 'none';
                document.getElementById('downloadExcelBtn').style.display = 'none';

                try {
                    // Fetch and parse Excel file
                    const response = await fetch(fileUrl);
                    if (!response.ok) throw new Error('Gagal memuat file');

                    const arrayBuffer = await response.arrayBuffer();
                    const data = new Uint8Array(arrayBuffer);
                    currentWorkbook = XLSX.read(data, {
                        type: 'array'
                    });

                    // Populate sheet selector
                    const sheetSelector = document.getElementById('sheetSelector');
                    sheetSelector.innerHTML = '';
                    currentWorkbook.SheetNames.forEach((sheetName, index) => {
                        const option = document.createElement('option');
                        option.value = index;
                        option.textContent = sheetName;
                        sheetSelector.appendChild(option);
                    });

                    // Show first sheet
                    displaySheet(0);

                    // Show preview content
                    document.getElementById('excelLoadingSpinner').style.display = 'none';
                    document.getElementById('excelPreviewContent').style.display = 'block';
                    document.getElementById('downloadExcelBtn').style.display = 'inline-block';

                } catch (error) {
                    console.error('Error loading Excel:', error);
                    document.getElementById('excelLoadingSpinner').style.display = 'none';
                    document.getElementById('excelErrorMessage').style.display = 'block';
                    document.getElementById('errorText').textContent = 'Gagal memuat file Excel: ' +
                        error.message;
                }
            });
        });

        // Handle sheet selector change
        document.getElementById('sheetSelector').addEventListener('change', function() {
            displaySheet(parseInt(this.value));
        });

        // Display selected sheet
        function displaySheet(sheetIndex) {
            if (!currentWorkbook) return;

            const sheetName = currentWorkbook.SheetNames[sheetIndex];
            const worksheet = currentWorkbook.Sheets[sheetName];

            // Convert to HTML table
            const html = XLSX.utils.sheet_to_html(worksheet, {
                header: '',
                footer: ''
            });

            // Apply Bootstrap table classes
            const styledHtml = html.replace(
                '<table>',
                '<table class="table table-bordered table-striped table-hover table-sm">'
            );

            document.getElementById('excelTableContainer').innerHTML = styledHtml;
        }

        // Handle download button
        document.getElementById('downloadExcelBtn').addEventListener('click', function() {
            if (currentFileUrl) {
                window.location.href = currentFileUrl;
            }
        });
    </script>
@endpush
