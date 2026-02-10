@extends('layouts.app')

@section('title', 'Tahapan Pelaksanaan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Pelaksanaan Fasilitasi / Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail</a></li>
                        <li class="breadcrumb-item active">Tahapan Pelaksanaan</li>
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
        @if (!$permohonan->penetapanJadwal)
            <div class="alert alert-info">
                <i class='bx bx-info-circle me-2'></i>
                Tab ini akan aktif setelah jadwal fasilitasi ditetapkan oleh Kaban.
            </div>
        @else
            <!-- Upload Dokumen Pelaksanaan -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class='bx bx-file me-1'></i>Dokumenentasi Pelaksanaan</h6>
                        <div class="d-flex gap-2">
                            @if (auth()->user()->hasAnyRole(['fasilitator', 'koordinator', 'verifikator', 'admin_peran', 'superadmin']))
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#uploadDokumenPelaksanaanModal">
                                    <i class='bx bx-plus-circle me-1'></i>Upload Dokumen
                                </button>
                            @endif

                            @php
                                // Cek status tahapan pelaksanaan untuk tombol selesaikan
                                $masterTahapanPelaksanaan = \App\Models\MasterTahapan::where(
                                    'nama_tahapan',
                                    'Pelaksanaan',
                                )->first();
                                $currentTahapan = null;
                                $isCompleted = false;
                                if ($masterTahapanPelaksanaan) {
                                    $currentTahapan = \App\Models\PermohonanTahapan::where(
                                        'permohonan_id',
                                        $permohonan->id,
                                    )
                                        ->where('tahapan_id', $masterTahapanPelaksanaan->id)
                                        ->first();
                                    $isCompleted = $currentTahapan && $currentTahapan->status === 'selesai';
                                }
                            @endphp

                            @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']) && !$isCompleted)
                                @php
                                    // Hitung dokumen untuk validasi tombol
                                    $dokumenCount = $permohonan
                                        ->dokumenTahapan()
                                        ->where('tahapan_id', $masterTahapanPelaksanaan->id ?? 0)
                                        ->count();
                                @endphp

                                @if ($dokumenCount > 0)
                                    <form action="{{ route('pelaksanaan-fasilitasi.complete', $permohonan) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menyelesaikan tahapan Pelaksanaan? Tahapan Hasil Fasilitasi akan dimulai.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class='bx bx-check-double me-1'></i>Selesaikan Tahapan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" disabled
                                        title="Upload minimal 1 dokumen terlebih dahulu">
                                        <i class='bx bx-check-double me-1'></i>Selesaikan Tahapan
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        // Get dokumen pelaksanaan
                        $dokumenPelaksanaan = collect();
                        if ($masterTahapanPelaksanaan) {
                            $dokumenPelaksanaan = $permohonan
                                ->dokumenTahapan()
                                ->where('tahapan_id', $masterTahapanPelaksanaan->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
                        }
                    @endphp

                    @if (isset($isCompleted) && $isCompleted && $currentTahapan)
                        <div class="alert alert-success mb-3">
                            <i class='bx bx-check-circle me-2'></i>
                            Tahapan Pelaksanaan telah diselesaikan pada
                            {{ $currentTahapan->updated_at ? $currentTahapan->updated_at->format('d F Y, H:i') . " WIT" : '-' }}
                        </div>
                    @endif

                    @if ($dokumenPelaksanaan && $dokumenPelaksanaan->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="35%">Jenis Dokumen</th>
                                        <th width="25%">Diupload Oleh</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="25%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dokumenPelaksanaan as $index => $dokumen)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                @php
                                                    $extension = strtolower(
                                                        pathinfo($dokumen->file_name, PATHINFO_EXTENSION),
                                                    );
                                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png']);
                                                    $iconClass = 'bx-file';
                                                    $iconColor = 'text-secondary';

                                                    if ($isImage) {
                                                        $iconClass = 'bx-image';
                                                        $iconColor = 'text-success';
                                                    } elseif ($extension === 'pdf') {
                                                        $iconClass = 'bxs-file-pdf';
                                                        $iconColor = 'text-danger';
                                                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                        $iconClass = 'bx-spreadsheet';
                                                        $iconColor = 'text-success';
                                                    } elseif ($extension === 'pptx') {
                                                        $iconClass = 'bx-slideshow';
                                                        $iconColor = 'text-warning';
                                                    }
                                                @endphp
                                                <div class="d-flex align-items-center gap-2">
                                                    <!-- Preview Thumbnail -->
                                                    <div class="preview-thumbnail"
                                                        style="width: 50px; height: 50px; cursor: pointer; border: 1px solid #dee2e6; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa;"
                                                        ondblclick="showPreview('{{ $dokumen->id }}', '{{ $isImage }}', '{{ asset('storage/' . $dokumen->file_path) }}', '{{ $dokumen->nama_dokumen }}', '{{ $extension }}')"
                                                        title="Klik dua kali untuk melihat preview">
                                                        @if ($isImage)
                                                            <img src="{{ asset('storage/' . $dokumen->file_path) }}"
                                                                alt="Preview"
                                                                style="width: 100%; height: 100%; object-fit: cover;">
                                                        @else
                                                            <i class='bx {{ $iconClass }} {{ $iconColor }}'
                                                                style="font-size: 24px;"></i>
                                                        @endif
                                                    </div>
                                                    <!-- Document Name -->
                                                    <div>
                                                        {{ $dokumen->nama_dokumen }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $dokumen->uploadedBy->name ?? '-' }}</td>
                                            <td>{{ $dokumen->created_at ? $dokumen->created_at->format('d M Y') : '-' }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="showPreview('{{ $dokumen->id }}', '{{ $isImage }}', '{{ asset('storage/' . $dokumen->file_path) }}', '{{ $dokumen->nama_dokumen }}', '{{ $extension }}')"
                                                    title="Lihat">
                                                    <i class='bx bx-show'></i>
                                                </button>
                                                <a href="{{ route('permohonan.dokumen.download', [$permohonan, $dokumen->id]) }}"
                                                    class="btn btn-sm btn-outline-primary" target="_blank" title="Download">
                                                    <i class='bx bx-download'></i>
                                                </a>
                                                @if (auth()->user()->hasAnyRole(['fasilitator', 'koordinator', 'verifikator', 'admin_peran', 'superadmin']))
                                                    <form
                                                        action="{{ route('permohonan.dokumen.delete', [$permohonan, $dokumen->id]) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="Hapus">
                                                            <i class='bx bx-trash'></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class='bx bx-file bx-lg text-muted mb-3 d-block'></i>
                            <p class="text-muted mb-0">Belum ada dokumen pelaksanaan yang diupload.</p>
                            @if (auth()->user()->hasAnyRole(['fasilitator', 'koordinator', 'verifikator', 'admin_peran', 'superadmin']))
                                <small class="text-muted">Klik tombol "Upload Dokumen" untuk menambahkan.</small>
                            @endif
                        </div>
                    @endif

                    <!-- Daftar Jenis Dokumen yang Dibutuhkan -->
                    <div class="alert alert-light border mt-4">
                        <strong><i class='bx bx-list-ul me-1'></i>Dokumen yang Diperlukan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Daftar Hadir Peserta</li>
                            <li>Sambutan Pembukaan</li>
                            <li>Materi Presentasi</li>
                            <li>Berita Acara Pelaksanaan</li>
                            <li>Notulensi Rapat</li>
                            <li>Dokumentasi Foto Kegiatan</li>
                            <li>Dokumen Pendukung Lainnya</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modal Upload Dokumen Pelaksanaan -->
            @if (auth()->user()->hasAnyRole(['fasilitator', 'koordinator', 'verifikator', 'admin_peran', 'superadmin']))
                <div class="modal fade" id="uploadDokumenPelaksanaanModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class='bx bx-upload me-1'></i>Upload Dokumen Pelaksanaan
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('permohonan.dokumen.upload-pelaksanaan', $permohonan->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="jenis_dokumen" class="form-label">Jenis Dokumen <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('jenis_dokumen') is-invalid @enderror"
                                            id="jenis_dokumen" name="jenis_dokumen" required>
                                            <option value="">Pilih Jenis Dokumen</option>
                                            <option value="Daftar Hadir">Daftar Hadir Peserta</option>
                                            <option value="Sambutan">Sambutan Pembukaan</option>
                                            <option value="Materi Presentasi">Materi Presentasi</option>
                                            <option value="Berita Acara">Berita Acara Pelaksanaan</option>
                                            <option value="Notulensi">Notulensi Rapat</option>
                                            <option value="Dokumentasi Foto">Dokumentasi Foto</option>
                                            <option value="Lainnya">Dokumen Lainnya</option>
                                        </select>
                                        @error('jenis_dokumen')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="file" class="form-label">File Dokumen <span
                                                class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                                            id="file" name="file" accept=".pdf,.xls,.xlsx,.pptx,.jpg,.jpeg,.png"
                                            required>
                                        <div class="form-text">
                                            Format: PDF, XLS, XLSX, PPTX, JPG, JPEG, PNG. Maksimal 10MB.
                                        </div>
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class='bx bx-upload me-1'></i>Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endif

    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Preview Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" id="previewContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- SheetJS Library for Excel Preview -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <script>
        function showPreview(id, isImage, filePath, dokumenName, extension) {
            const previewContent = document.getElementById('previewContent');
            const modalLabel = document.getElementById('previewModalLabel');

            modalLabel.textContent = dokumenName;

            if (isImage === '1' || isImage === 'true') {
                // Show image preview
                previewContent.innerHTML = `
            <img src="${filePath}" 
                alt="${dokumenName}" 
                class="img-fluid" 
                style="max-height: 70vh; width: auto;">
        `;
            } else if (extension === 'pdf') {
                // Show PDF preview
                previewContent.innerHTML = `
            <iframe src="${filePath}" 
                style="width: 100%; height: 70vh; border: none;"
                title="${dokumenName}">
            </iframe>
        `;
            } else if (extension === 'xls' || extension === 'xlsx') {
                // Show loading message
                previewContent.innerHTML = `
            <div class="py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Memuat preview Excel...</p>
            </div>
        `;

                // Fetch and preview Excel file using SheetJS
                fetch(filePath)
                    .then(response => response.arrayBuffer())
                    .then(data => {
                        const workbook = XLSX.read(data, {
                            type: 'array'
                        });
                        const firstSheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[firstSheetName];
                        const html = XLSX.utils.sheet_to_html(worksheet, {
                            id: 'excel-preview-table',
                            editable: false
                        });

                        previewContent.innerHTML = `
                    <div class="text-start" style="max-height: 70vh; overflow: auto;">
                        <div class="alert alert-info mb-3">
                            <i class='bx bx-info-circle'></i> Preview sheet: <strong>${firstSheetName}</strong>
                            ${workbook.SheetNames.length > 1 ? ` (Total ${workbook.SheetNames.length} sheets)` : ''}
                        </div>
                        <div class="table-responsive">
                            ${html}
                        </div>
                    </div>
                `;

                        // Style the table
                        const table = document.getElementById('excel-preview-table');
                        if (table) {
                            table.classList.add('table', 'table-bordered', 'table-sm', 'table-hover');
                        }
                    })
                    .catch(error => {
                        previewContent.innerHTML = `
                    <div class="py-5">
                        <i class='bx bx-error text-danger' style="font-size: 80px;"></i>
                        <h5 class="mt-3">Gagal memuat preview</h5>
                        <p class="text-muted">${error.message}</p>
                        <a href="${filePath}" class="btn btn-primary" target="_blank">
                            <i class='bx bx-download'></i> Download untuk Melihat
                        </a>
                    </div>
                `;
                    });
            } else if (extension === 'pptx') {
                // For PowerPoint, show message with download option
                previewContent.innerHTML = `
            <div class="py-5">
                <i class='bx bx-slideshow text-warning' style="font-size: 80px;"></i>
                <h5 class="mt-3">${dokumenName}</h5>
                <p class="text-muted">File PowerPoint tidak dapat ditampilkan preview di browser.</p>
                <a href="${filePath}" class="btn btn-primary" target="_blank">
                    <i class='bx bx-download'></i> Download untuk Melihat
                </a>
            </div>
        `;
            } else {
                // For DOC/DOCX and others, show message with download option
                const iconClass = extension === 'doc' || extension === 'docx' ? 'bxs-file-doc' : 'bx-file';
                const iconColor = extension === 'doc' || extension === 'docx' ? 'text-primary' : 'text-secondary';

                previewContent.innerHTML = `
            <div class="py-5">
                <i class='bx ${iconClass} ${iconColor}' style="font-size: 80px;"></i>
                    <p class="text-muted">File ${extension.toUpperCase()} tidak dapat ditampilkan preview.</p>
                    <a href="${filePath}" class="btn btn-primary" target="_blank">
                        <i class='bx bx-download'></i> Download untuk Melihat
                    </a>
                </div>
            `;
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }
    </script>
@endpush
