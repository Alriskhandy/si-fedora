@extends('layouts.app')

@use('Illuminate\Support\Facades\Storage')

@push('styles')
    <style>
        /* Table row hover effect */
        .document-row {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .document-row:hover {
            background-color: #f8f9fa;
        }

        .document-row.active {
            background-color: #e7f3ff;
            border-left: 3px solid #4e73df;
        }

        /* Avatar sizing */
        .avatar-sm {
            width: 38px;
            height: 38px;
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Badge improvements */
        .badge {
            font-weight: 500;
        }

        /* Card improvements */
        .card {
            transition: all 0.3s ease;
        }

        /* Preview area */
        .preview-area {
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .preview-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            color: #6c757d;
        }

        .preview-placeholder i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .document-list-card {
            max-height: 450px;
            overflow-y: auto;
        }

        .document-list-card::-webkit-scrollbar {
            width: 6px;
        }

        .document-list-card::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 3px;
        }

        .document-list-card::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }
    </style>
@endpush

@section('title', 'Detail Arsip Dokumen')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Arsip Dokumen</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('arsip.index') }}">Arsip Dokumen</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <!-- 2 Column Layout -->
        <div class="row">
            <!-- Left Column: Document Lists -->
            <div class="col-lg-7">
                <!-- Card 1: Hasil Fasilitasi -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0 text-white">
                            <i class='bx bx-file-find me-2'></i>
                            Hasil Fasilitasi
                        </h5>
                        <p class="small mb-0 mt-1 opacity-75">Dokumen hasil fasilitasi, tindak lanjut, dan penetapan</p>
                    </div>
                    <div class="card-body p-0 document-list-card">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="px-4">No</th>
                                        <th style="width: 40%;">Nama Dokumen</th>
                                        <th style="width: 15%;" class="text-center">Jenis</th>
                                        <th style="width: 15%;" class="text-center">Ukuran</th>
                                        <th style="width: 25%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $rowNum = 1;
                                    @endphp

                                    {{-- Hasil Fasilitasi --}}
                                    @if ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->draft_final_file)
                                        <tr class="document-row"
                                            data-file-url="{{ Storage::url($permohonan->hasilFasilitasi->draft_final_file) }}"
                                            data-file-name="Dokumen Hasil Fasilitasi">
                                            <td class="px-4">{{ $rowNum++ }}</td>
                                            <td>
                                                <div class="fw-semibold">Dokumen Hasil Fasilitasi</div>
                                                <small class="text-muted">Hasil evaluasi tim pokja</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-danger">PDF</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $filePath = storage_path(
                                                        'app/public/' . $permohonan->hasilFasilitasi->draft_final_file,
                                                    );
                                                    $fileSize = file_exists($filePath)
                                                        ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                        : '0';
                                                @endphp
                                                {{ $fileSize }} MB
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                    data-file-url="{{ Storage::url($permohonan->hasilFasilitasi->draft_final_file) }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ Storage::url($permohonan->hasilFasilitasi->draft_final_file) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- Tindak Lanjut --}}
                                    @php
                                        $dokumenTindakLanjut = $permohonan->dokumenTahapan
                                            ->where('tahapan_id', 6)
                                            ->filter(function ($dok) {
                                                return $dok->file_path;
                                            });
                                    @endphp
                                    @foreach ($dokumenTindakLanjut as $dok)
                                        <tr class="document-row" data-file-url="{{ Storage::url($dok->file_path) }}"
                                            data-file-name="{{ $dok->nama_dokumen ?? 'Dokumen Tindak Lanjut' }}">
                                            <td class="px-4">{{ $rowNum++ }}</td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $dok->nama_dokumen ?? 'Dokumen Tindak Lanjut' }}</div>
                                                <small class="text-muted">Tindak lanjut hasil fasilitasi</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-danger">PDF</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $filePath = storage_path('app/public/' . $dok->file_path);
                                                    $fileSize = file_exists($filePath)
                                                        ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                        : '0';
                                                @endphp
                                                {{ $fileSize }} MB
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                    data-file-url="{{ Storage::url($dok->file_path) }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Penetapan Perda --}}
                                    @php
                                        $dokumenPenetapan = $permohonan->dokumenTahapan
                                            ->where('tahapan_id', 7)
                                            ->filter(function ($dok) {
                                                return $dok->file_path;
                                            });
                                    @endphp
                                    @foreach ($dokumenPenetapan as $dok)
                                        <tr class="document-row" data-file-url="{{ Storage::url($dok->file_path) }}"
                                            data-file-name="{{ $dok->nama_dokumen ?? 'Dokumen Penetapan Perda' }}">
                                            <td class="px-4">{{ $rowNum++ }}</td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $dok->nama_dokumen ?? 'Dokumen Penetapan Perda' }}</div>
                                                <small class="text-muted">Dokumen penetapan peraturan daerah</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-danger">PDF</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $filePath = storage_path('app/public/' . $dok->file_path);
                                                    $fileSize = file_exists($filePath)
                                                        ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                        : '0';
                                                @endphp
                                                {{ $fileSize }} MB
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                    data-file-url="{{ Storage::url($dok->file_path) }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($rowNum == 1)
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class='bx bx-info-circle me-1'></i>
                                                Belum ada dokumen
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Persyaratan & Kelengkapan -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="card-title mb-0">
                            <i class='bx bx-folder me-2'></i>
                            Persyaratan & Kelengkapan
                        </h5>
                        <p class="small mb-0 mt-1 opacity-75">Dokumen permohonan dan verifikasi</p>
                    </div>
                    <div class="card-body p-0 document-list-card">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="px-4">No</th>
                                        <th style="width: 40%;">Nama Dokumen</th>
                                        <th style="width: 15%;" class="text-center">Jenis</th>
                                        <th style="width: 15%;" class="text-center">Ukuran</th>
                                        <th style="width: 25%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $rowNum = 1;
                                    @endphp

                                    {{-- Dokumen Permohonan --}}
                                    @if ($permohonan->permohonanDokumen && $permohonan->permohonanDokumen->count() > 0)
                                        @foreach ($permohonan->permohonanDokumen as $dok)
                                            @if ($dok->file_path)
                                                @php
                                                    $fileExtension = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                                    $fileTypeLabel = match($fileExtension) {
                                                        'pdf' => ['label' => 'PDF', 'class' => 'bg-label-danger'],
                                                        'xlsx', 'xls' => ['label' => 'EXCEL', 'class' => 'bg-label-success'],
                                                        default => ['label' => strtoupper($fileExtension), 'class' => 'bg-label-secondary']
                                                    };
                                                @endphp
                                                <tr class="document-row"
                                                    data-file-url="{{ Storage::url($dok->file_path) }}"
                                                    data-file-name="{{ $dok->masterKelengkapan->nama_dokumen ?? 'Dokumen Permohonan' }}">
                                                    <td class="px-4">{{ $rowNum++ }}</td>
                                                    <td>
                                                        <div class="fw-semibold">
                                                            {{ $dok->masterKelengkapan->nama_dokumen ?? 'Dokumen' }}
                                                        </div>
                                                        <small class="text-muted">Dokumen permohonan</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $fileTypeLabel['class'] }}">{{ $fileTypeLabel['label'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $filePath = storage_path('app/public/' . $dok->file_path);
                                                            $fileSize = file_exists($filePath)
                                                                ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                                : '0';
                                                        @endphp
                                                        {{ $fileSize }} MB
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                            data-file-url="{{ Storage::url($dok->file_path) }}">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                        <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                                                            class="btn btn-sm btn-outline-success">
                                                            <i class="bx bx-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif

                                    {{-- Dokumen Kelengkapan Lainnya dari Tahapan Permohonan --}}
                                    @if ($permohonan->dokumenTahapan && $permohonan->dokumenTahapan->count() > 0)
                                        @foreach ($permohonan->dokumenTahapan as $dok)
                                            @if ($dok->file_path && $dok->tahapan_id == 1)
                                                @php
                                                    $fileExtension = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                                    $fileTypeLabel = match($fileExtension) {
                                                        'pdf' => ['label' => 'PDF', 'class' => 'bg-label-danger'],
                                                        'xlsx', 'xls' => ['label' => 'EXCEL', 'class' => 'bg-label-success'],
                                                        default => ['label' => strtoupper($fileExtension), 'class' => 'bg-label-secondary']
                                                    };
                                                @endphp
                                                <tr class="document-row"
                                                    data-file-url="{{ Storage::url($dok->file_path) }}"
                                                    data-file-name="{{ $dok->nama_dokumen ?? 'Dokumen Kelengkapan' }}">
                                                    <td class="px-4">{{ $rowNum++ }}</td>
                                                    <td>
                                                        <div class="fw-semibold">
                                                            {{ $dok->nama_dokumen ?? 'Dokumen' }}</div>
                                                        <small class="text-muted">Dokumen kelengkapan tambahan</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $fileTypeLabel['class'] }}">{{ $fileTypeLabel['label'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $filePath = storage_path('app/public/' . $dok->file_path);
                                                            $fileSize = file_exists($filePath)
                                                                ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                                : '0';
                                                        @endphp
                                                        {{ $fileSize }} MB
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                            data-file-url="{{ Storage::url($dok->file_path) }}">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                        <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                                                            class="btn btn-sm btn-outline-success">
                                                            <i class="bx bx-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif

                                    {{-- Laporan Verifikasi --}}
                                    @if ($permohonan->laporanVerifikasi && $permohonan->laporanVerifikasi->file_laporan)
                                        <tr class="document-row"
                                            data-file-url="{{ Storage::url($permohonan->laporanVerifikasi->file_laporan) }}"
                                            data-file-name="Laporan Verifikasi">
                                            <td class="px-4">{{ $rowNum++ }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class='bx bx-file-blank text-danger me-2 fs-5'></i>
                                                    <div>
                                                        <div class="fw-semibold">Laporan Verifikasi</div>
                                                        <small class="text-muted">Hasil verifikasi</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-danger">PDF</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $filePath = storage_path('app/public/' . $permohonan->laporanVerifikasi->file_laporan);
                                                    $fileSize = file_exists($filePath)
                                                        ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                        : '0';
                                                @endphp
                                                {{ $fileSize }} MB
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                    data-file-url="{{ Storage::url($permohonan->laporanVerifikasi->file_laporan) }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ Storage::url($permohonan->laporanVerifikasi->file_laporan) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($rowNum == 1)
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class='bx bx-info-circle me-1'></i>
                                                Belum ada dokumen
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 3: File/Dokumen Lainnya -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="card-title mb-0 text-white">
                            <i class='bx bx-folder-open me-2'></i>
                            File & Dokumen Lainnya
                        </h5>
                        <p class="small mb-0 mt-1 opacity-75">Jadwal, undangan, dan surat-surat</p>
                    </div>
                    <div class="card-body p-0 document-list-card">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="px-4">No</th>
                                        <th style="width: 40%;">Nama Dokumen</th>
                                        <th style="width: 15%;" class="text-center">Jenis</th>
                                        <th style="width: 15%;" class="text-center">Ukuran</th>
                                        <th style="width: 25%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $rowNum = 1;
                                        $hasDocuments = false;
                                    @endphp

                                    {{-- Dokumentasi Pelaksanaan --}}
                                    @php
                                        $dokumenPelaksanaan = $permohonan->dokumenTahapan
                                            ->where('tahapan_id', 4)
                                            ->filter(function ($dok) {
                                                return $dok->file_path;
                                            });
                                    @endphp
                                    @foreach ($dokumenPelaksanaan as $dok)
                                        @php
                                            $hasDocuments = true;
                                        @endphp
                                        <tr class="document-row" data-file-url="{{ Storage::url($dok->file_path) }}"
                                            data-file-name="{{ $dok->nama_dokumen ?? 'Dokumentasi Pelaksanaan' }}">
                                            <td class="px-4">{{ $rowNum++ }}</td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $dok->nama_dokumen ?? 'Dokumentasi Pelaksanaan' }}</div>
                                                <small class="text-muted">Dokumentasi pelaksanaan fasilitasi</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-danger">PDF</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $filePath = storage_path('app/public/' . $dok->file_path);
                                                    $fileSize = file_exists($filePath)
                                                        ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                        : '0';
                                                @endphp
                                                {{ $fileSize }} MB
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                    data-file-url="{{ Storage::url($dok->file_path) }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Undangan Pelaksanaan --}}
                                    @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->count() > 0)
                                        @foreach ($permohonan->undanganPelaksanaan as $undangan)
                                            @if ($undangan->file_undangan)
                                                @php
                                                    $hasDocuments = true;
                                                @endphp
                                                <tr class="document-row"
                                                    data-file-url="{{ Storage::url($undangan->file_undangan) }}"
                                                    data-file-name="Undangan Pelaksanaan">
                                                    <td class="px-4">{{ $rowNum++ }}</td>
                                                    <td>
                                                        <div class="fw-semibold">Undangan Pelaksanaan</div>
                                                        <small
                                                            class="text-muted">{{ $undangan->nomor_undangan ?? 'Undangan' }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-label-danger">PDF</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $filePath = storage_path(
                                                                'app/public/' . $undangan->file_undangan,
                                                            );
                                                            $fileSize = file_exists($filePath)
                                                                ? number_format(filesize($filePath) / (1024 * 1024), 2)
                                                                : '0';
                                                        @endphp
                                                        {{ $fileSize }} MB
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary preview-btn me-1"
                                                            data-file-url="{{ Storage::url($undangan->file_undangan) }}">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                        <a href="{{ Storage::url($undangan->file_undangan) }}"
                                                            target="_blank" class="btn btn-sm btn-outline-success">
                                                            <i class="bx bx-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif

                                    @if (!$hasDocuments)
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class='bx bx-info-circle me-1'></i>
                                                Belum ada dokumen
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Preview Area -->
            <div class="col-lg-5">
                <div class="preview-area">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">
                                <i class='bx bx-show me-2 text-primary'></i>
                                Preview Dokumen
                            </h5>
                        </div>
                        <div class="card-body p-0" id="preview-container">
                            <div class="preview-placeholder">
                                <i class='bx bx-file'></i>
                                <h5 class="text-muted">Belum ada dokumen dipilih</h5>
                                <p class="text-muted small">Klik pada dokumen di samping untuk melihat preview</p>
                            </div>
                        </div>
                    </div>
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
    <!-- SheetJS Library -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle preview button clicks
            const previewButtons = document.querySelectorAll('.preview-btn');
            const documentRows = document.querySelectorAll('.document-row');
            const previewContainer = document.getElementById('preview-container');

            function showPreview(fileUrl, fileName) {
                const extension = fileUrl.split('.').pop().toLowerCase();

                let previewHtml = '';

                if (extension === 'pdf') {
                    previewHtml = `
                            <div class="p-3">
                                <div class="alert alert-info mb-3">
                                    <i class='bx bx-info-circle me-2'></i>
                                    <strong>${fileName}</strong>
                                </div>
                                <iframe src="${fileUrl}" style="width: 100%; height: 600px; border: none;" frameborder="0"></iframe>
                            </div>
                        `;
                } else if (['xlsx', 'xls'].includes(extension)) {
                    // Show Excel in modal
                    showExcelPreview(fileUrl, fileName);
                    return;
                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                    previewHtml = `
                            <div class="p-3">
                                <div class="alert alert-info mb-3">
                                    <i class='bx bx-info-circle me-2'></i>
                                    <strong>${fileName}</strong>
                                </div>
                                <img src="${fileUrl}" class="img-fluid" alt="${fileName}">
                            </div>
                        `;
                } else {
                    previewHtml = `
                            <div class="preview-placeholder">
                                <i class='bx bx-file'></i>
                                <h5 class="text-muted">Preview tidak tersedia</h5>
                                <p class="text-muted small">Format file ini tidak dapat ditampilkan</p>
                                <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                    <i class='bx bx-download me-1'></i> Download File
                                </a>
                            </div>
                        `;
                }

                previewContainer.innerHTML = previewHtml;
            }

            previewButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const fileUrl = this.getAttribute('data-file-url');
                    const fileName = this.closest('tr').getAttribute('data-file-name') || 'Dokumen';

                    // Remove active class from all rows
                    documentRows.forEach(row => row.classList.remove('active'));

                    // Add active class to clicked row
                    this.closest('tr').classList.add('active');

                    showPreview(fileUrl, fileName);
                });
            });

            // Handle row clicks
            documentRows.forEach(row => {
                row.addEventListener('click', function() {
                    const fileUrl = this.getAttribute('data-file-url');
                    const fileName = this.getAttribute('data-file-name') || 'Dokumen';

                    if (fileUrl) {
                        // Remove active class from all rows
                        documentRows.forEach(r => r.classList.remove('active'));

                        // Add active class to clicked row
                        this.classList.add('active');

                        showPreview(fileUrl, fileName);
                    }
                });
            });
        });

        // ============================================
        // EXCEL PREVIEW FUNCTIONALITY
        // ============================================
        let currentWorkbook = null;
        let currentFileUrl = null;

        async function showExcelPreview(fileUrl, fileName) {
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
                document.getElementById('errorText').textContent = 'Gagal memuat file Excel: ' + error.message;
            }
        }

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
