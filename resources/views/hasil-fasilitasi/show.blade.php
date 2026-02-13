@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold">Detail Hasil Fasilitasi / Evaluasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hasil-fasilitasi.index') }}">Hasil Fasilitasi /
                                Evaluasi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('hasil-fasilitasi.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible position-fixed top-0 end-0 m-3" role="alert"
                style="z-index: 9999; max-width: 400px;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible position-fixed top-0 end-0 m-3" role="alert"
                style="z-index: 9999; max-width: 400px;">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <!-- Informasi Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Get tahapan pelaksanaan
                            $masterTahapanPelaksanaan = \App\Models\MasterTahapan::where(
                                'nama_tahapan',
                                'Pelaksanaan',
                            )->first();
                            $tahapanPelaksanaan = null;
                            if ($masterTahapanPelaksanaan) {
                                $tahapanPelaksanaan = $permohonan
                                    ->tahapan()
                                    ->where('tahapan_id', $masterTahapanPelaksanaan->id)
                                    ->first();
                            }

                            // Get tahapan hasil/evaluasi
                            $masterTahapanHasil = \App\Models\MasterTahapan::where(
                                'nama_tahapan',
                                'Hasil Fasilitasi / Evaluasi',
                            )->first();
                            $tahapanHasil = null;
                            if ($masterTahapanHasil) {
                                $tahapanHasil = $permohonan
                                    ->tahapan()
                                    ->where('tahapan_id', $masterTahapanHasil->id)
                                    ->first();
                            }
                        @endphp
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kabupaten/Kota</dt>
                            <dd class="col-sm-7">{{ $permohonan->kabupatenKota->nama }}</dd>

                            <dt class="col-sm-5">Jenis Dokumen</dt>
                            <dd class="col-sm-7"><span
                                    class="badge bg-primary">{{ strtoupper($permohonan->jenisDokumen->nama) }}</span></dd>

                            <dt class="col-sm-5">Tahun</dt>
                            <dd class="col-sm-7">{{ $permohonan->tahun }}</dd>

                            @if ($tahapanPelaksanaan && $tahapanPelaksanaan->updated_at)
                                <dt class="col-sm-5">Waktu Pelaksanaan</dt>
                                <dd class="col-sm-7">
                                    {{ $tahapanPelaksanaan->updated_at->format('d M Y, H:i') }}
                                    <span class="badge bg-success">{{ $tahapanPelaksanaan->status }}</span>
                                </dd>
                            @endif

                            @if ($tahapanHasil && $tahapanHasil->deadline)
                                <dt class="col-sm-5">Batas Waktu Penginputan</dt>
                                <dd class="col-sm-7">
                                    @php
                                        $deadline = \Carbon\Carbon::parse($tahapanHasil->deadline);
                                        $isOverdue = \Carbon\Carbon::now()->gt($deadline);
                                    @endphp
                                    <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning' }}">
                                        <i class="bx {{ $isOverdue ? 'bx-error-circle' : 'bx-time' }}"></i>
                                        {{ $deadline->format('d M Y, H:i') }}
                                        @if ($isOverdue)
                                            (Terlewat)
                                        @endif
                                    </span>
                                </dd>
                            @endif

                            <dt class="col-sm-5">Sistematika</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-primary">{{ $hasilFasilitasi->hasilSistematika->count() }}</span>
                                item
                            </dd>

                            <dt class="col-sm-5">Urusan</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-primary">{{ $hasilFasilitasi->hasilUrusan->count() }}</span> item
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Informasi Tim -->
                @if ($timInfo)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bx bx-group"></i> Tim Fasilitasi / Evaluasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    @if ($timInfo['verifikator'])
                                        <div class="mb-2">
                                            <small class="text-muted">PIC (Verifikator):</small>
                                            <div><strong>{{ $timInfo['verifikator']->user->name }}</strong></div>
                                        </div>
                                    @endif

                                    @if ($timInfo['koordinator'])
                                        <div class="mb-2">
                                            <small class="text-muted">Koordinator :</small>
                                            <div>
                                                <strong>{{ $timInfo['koordinator']->user->name }}</strong>
                                                @if ($timInfo['koordinator']->user_id == Auth::id())
                                                    <span class="badge bg-success">Anda</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    @if ($timInfo['anggota']->count() > 0)
                                        <div class="mb-0">
                                            <small class="text-muted">Anggota :</small>
                                            @foreach ($timInfo['anggota'] as $anggota)
                                                <div>
                                                    - {{ $anggota->user->name }}
                                                    @if ($anggota->user_id == Auth::id())
                                                        <span class="badge bg-success">Anda</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Upload Draft Final (Admin/Koordinator Only) -->
        @if (($isAdmin || $isKoordinator) && $hasilFasilitasi && $hasilFasilitasi->draft_file)
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bx bx-file-pdf"></i> Draft Final Dokumen Hasil Fasilitasi
                            </h5>
                            <small class="text-muted">
                                Upload file PDF final yang sudah dilengkapi dengan kop surat dan lampiran lainnya
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    @if ($hasilFasilitasi->draft_final_file)
                                        <div class="alert">
                                            <i class="bx bx-check-circle"></i> <strong>Draft final sudah diupload</strong>
                                            <div class="mt-2">
                                                <a href="{{ route('hasil-fasilitasi.download-draft-final', $permohonan->id) }}"
                                                    class="btn btn-sm btn-success me-2">
                                                    <i class="bx bx-download"></i> Download Draft Final
                                                </a>
                                                <button type="button" class="btn btn-sm btn-warning"
                                                    onclick="document.getElementById('formUploadDraftFinal').classList.remove('d-none')">
                                                    <i class="bx bx-refresh"></i> Upload Ulang
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle"></i> Draft final belum diupload. Silakan upload
                                            file PDF yang sudah dilengkapi dengan kop surat.
                                        </div>
                                    @endif

                                    <!-- Form Upload -->
                                    <form id="formUploadDraftFinal"
                                        action="{{ route('hasil-fasilitasi.upload-draft-final', $permohonan->id) }}"
                                        method="POST" enctype="multipart/form-data"
                                        class="{{ $hasilFasilitasi->draft_final_file ? 'd-none' : '' }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="draft_final_file" class="form-label">
                                                Upload Draft Final PDF <span class="text-danger">*</span>
                                            </label>
                                            <input type="file"
                                                class="form-control @error('draft_final_file') is-invalid @enderror"
                                                id="draft_final_file" name="draft_final_file" accept=".pdf" required>
                                            <small class="text-muted">Format: PDF, Maksimal: 10MB</small>
                                            @error('draft_final_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-upload"></i> Upload Draft Final
                                        </button>
                                        @if ($hasilFasilitasi->draft_final_file)
                                            <button type="button" class="btn btn-secondary"
                                                onclick="document.getElementById('formUploadDraftFinal').classList.add('d-none')">
                                                Batal
                                            </button>
                                        @endif
                                    </form>
                                </div>

                                <!-- Tombol Submit ke Kaban (Admin Only) -->
                                @if ($isAdmin && $hasilFasilitasi->draft_final_file)
                                    <div class="col-md-4">
                                        <div class="border-start ps-4">
                                            <h6 class="mb-2">Ajukan ke Kepala Badan</h6>
                                            @if ($hasilFasilitasi->status_draft === 'menunggu_persetujuan_kaban')
                                                <div class="alert alert-warning">
                                                    <i class="bx bx-time"></i> <strong>Menunggu Persetujuan</strong>
                                                    <p class="mb-0 small">
                                                        Diajukan:
                                                        {{ $hasilFasilitasi->tanggal_diajukan_kaban ? $hasilFasilitasi->tanggal_diajukan_kaban->format('d M Y, H:i') : '-' }}
                                                    </p>
                                                </div>
                                            @elseif ($hasilFasilitasi->status_draft === 'disetujui_kaban')
                                                <div class="alert alert-success">
                                                    <i class="bx bx-check-circle"></i> <strong>Sudah Disetujui</strong>
                                                    <p class="mb-0 small">
                                                        Tanggal:
                                                        {{ $hasilFasilitasi->tanggal_disetujui_kaban ? $hasilFasilitasi->tanggal_disetujui_kaban->format('d M Y, H:i') : '-' }}
                                                    </p>
                                                </div>
                                            @else
                                                <p class="text-muted small mb-3">
                                                    Draft final siap untuk diajukan ke Kepala Badan untuk mendapatkan
                                                    persetujuan.
                                                </p>
                                                <form
                                                    action="{{ route('hasil-fasilitasi.submit-to-kaban', $permohonan->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Ajukan dokumen ini ke Kepala Badan untuk persetujuan?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="bx bx-send"></i> Ajukan ke Kaban
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <!-- Tabs untuk Sistematika dan Urusan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bx bx-list-check"></i> Masukan Fasilitasi</h5>
                            @if ($hasilFasilitasi && ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                                <div>
                                    @if ($isKoordinator && (!isset($isVerifikator) || !$isVerifikator))
                                        @if ($hasilFasilitasi->draft_file)
                                            <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}"
                                                class="btn btn-sm btn-warning me-2" data-bs-toggle="tooltip"
                                                title="Buat ulang draft dokumen hasil masukan">
                                                <i class="bx bx-refresh"></i> Buat Ulang Draft
                                            </a>
                                        @else
                                            <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}"
                                                class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
                                                title="Buat draft dokumen hasil masukan">
                                                <i class="bx bx-file-blank"></i> Buat Draft Dokumen
                                            </a>
                                        @endif
                                    @endif

                                    @if ($hasilFasilitasi->draft_file)
                                        <button type="button" class="btn btn-sm btn-outline-info me-2"
                                            data-bs-toggle="modal" data-bs-target="#previewPdfModal"
                                            title="Lihat preview PDF">
                                            <i class="bx bx-show"></i> Lihat Draft
                                        </button>
                                        <a href="{{ route('hasil-fasilitasi.download-word', $permohonan->id) }}"
                                            class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="tooltip"
                                            title="Unduh draft dalam format Word">
                                            <i class="bx bx-download"></i> Word
                                        </a>
                                        <a href="{{ route('hasil-fasilitasi.download-pdf', $permohonan->id) }}"
                                            class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                            title="Unduh draft dalam format PDF">
                                            <i class="bx bx-download"></i> PDF
                                        </a>
                                    @else
                                        <small class="text-muted">
                                            <i class="bx bx-info-circle"></i>
                                            @if ($isKoordinator && (!isset($isVerifikator) || !$isVerifikator))
                                                Klik "Buat Draft Dokumen" untuk membuat draft hasil masukan
                                            @else
                                                Koordinator belum membuat draft dokumen
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="sistematika-tab" data-bs-toggle="tab"
                                    data-bs-target="#sistematika" type="button" role="tab">
                                    <i class="bx bx-book-content"></i> Sistematika & Rancangan Akhir
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="urusan-tab" data-bs-toggle="tab" data-bs-target="#urusan"
                                    type="button" role="tab">
                                    <i class="bx bx-list-ul"></i> Urusan Pemerintahan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-4">
                            <!-- Tab 1: Sistematika -->
                            <div class="tab-pane fade show active" id="sistematika" role="tabpanel">
                                @if ($hasilFasilitasi->hasilSistematika->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="20%">Bab / Sub Bab</th>
                                                    <th width="55%">Catatan Penyempurnaan</th>
                                                    <th width="20%">Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($hasilFasilitasi->hasilSistematika as $item)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td>
                                                            <strong>{{ $item->masterBab->nama_bab ?? '-' }}</strong>
                                                            @if ($item->sub_bab)
                                                                <br><span>{{ $item->sub_bab }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{!! is_string($item->catatan_penyempurnaan)
                                                            ? $item->catatan_penyempurnaan
                                                            : $item->catatan_penyempurnaan->render() !!}</td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $item->user->name ?? '-' }}<br>
                                                                <span
                                                                    class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada item sistematika.
                                    </div>
                                @endif
                            </div>

                            <!-- Tab 2: Urusan Pemerintahan -->
                            <div class="tab-pane fade" id="urusan" role="tabpanel">
                                @if ($hasilFasilitasi->hasilUrusan->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="25%">Urusan Pemerintahan</th>
                                                    <th width="50%">Catatan Masukan / Saran</th>
                                                    <th width="20%">Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($hasilFasilitasi->hasilUrusan as $item)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td>
                                                            <strong>
                                                                {{ $item->masterUrusan->urutan }}.
                                                                {{ $item->masterUrusan->nama_urusan }}
                                                            </strong>
                                                        </td>
                                                        <td>{!! is_string($item->catatan_masukan) ? $item->catatan_masukan : $item->catatan_masukan->render() !!}</td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $item->user->name ?? '-' }}<br>
                                                                <span
                                                                    class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada item urusan.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                @if ($hasilFasilitasi->catatan)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bx bx-note"></i> Catatan</h5>
                        </div>
                        <div class="card-body">
                            <div class="border rounded p-3" style="white-space: pre-line;">
                                {{ $hasilFasilitasi->catatan }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Preview PDF -->
    <div class="modal fade" id="previewPdfModal" tabindex="-1" aria-labelledby="previewPdfModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewPdfModalLabel">
                        <i class="bx bx-file-blank"></i> Preview Dokumen Hasil Fasilitasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    @if ($hasilFasilitasi && $hasilFasilitasi->draft_file)
                        <iframe id="pdfPreviewFrame"
                            src="{{ route('hasil-fasilitasi.preview-pdf', $permohonan->id) }}?v={{ $hasilFasilitasi->updated_at?->timestamp ?? time() }}"
                            width="100%" height="100%" style="border: none;"
                            title="Preview PDF Hasil Fasilitasi"></iframe>
                    @else
                        <div class="alert alert-info m-3">
                            <i class="bx bx-info-circle"></i> Draft dokumen belum tersedia.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <a href="{{ route('hasil-fasilitasi.download-pdf', $permohonan->id) }}" class="btn btn-primary">
                        <i class="bx bx-download"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Reload iframe when modal is opened to clear cache
        const previewModal = document.getElementById('previewPdfModal');
        if (previewModal) {
            previewModal.addEventListener('show.bs.modal', function() {
                const iframe = document.getElementById('pdfPreviewFrame');
                if (iframe) {
                    // Reload iframe with new timestamp to bypass cache
                    const currentSrc = iframe.src.split('?')[0];
                    iframe.src = currentSrc + '?v=' + new Date().getTime();
                }
            });
        }
    </script>
@endpush
