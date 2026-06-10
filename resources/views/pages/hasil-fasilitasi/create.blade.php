@extends('layouts.app')

@push('styles')
    <style>
        .tinymce-wrapper {
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold">
                    Input Hasil Fasilitasi / Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Hasil Fasilitasi / Evaluasi</a>
                        </li>
                        <li class="breadcrumb-item active">Input</li>
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
                                            <small class="text-muted">Format: PDF, Maksimal: 100MB</small>
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
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bx bx-list-check"></i> Masukan Fasilitasi / Evaluasi</h5>
                            @if ($hasilFasilitasi && ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                                <div>
                                    @if ($isKoordinator)
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
                                            @if ($isKoordinator)
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
                        @php
                            $sistematikUsers = $hasilFasilitasi->hasilSistematika->pluck('user')->unique('id')->filter()->values();
                            $formUsers       = $hasilFasilitasi->hasilForm->pluck('user')->unique('id')->filter()->values();
                            $urusanUsers     = $hasilFasilitasi->hasilUrusan->pluck('user')->unique('id')->filter()->values();
                            $rekomendasiUsers = $hasilFasilitasi->hasilRekomendasi->pluck('user')->unique('id')->filter()->values();
                        @endphp
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="sistematika-tab" data-bs-toggle="tab"
                                    data-bs-target="#sistematika" type="button" role="tab">
                                    <i class="bx bx-book-content"></i> Sistematika & Rancangan Akhir
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="form-tab" data-bs-toggle="tab" data-bs-target="#form"
                                    type="button" role="tab">
                                    <i class="bx bx-link"></i> Konsistensi & Keselarasan
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="urusan-tab" data-bs-toggle="tab" data-bs-target="#urusan"
                                    type="button" role="tab">
                                    <i class="bx bx-list-ul"></i> Urusan Pemerintahan
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="rekomendasi-tab" data-bs-toggle="tab"
                                    data-bs-target="#rekomendasi" type="button" role="tab">
                                    <i class="bx bx-check-shield"></i> Rekomendasi
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-4">
                            <!-- Tab 1: Sistematika -->
                            <div class="tab-pane fade show active" id="sistematika" role="tabpanel">
                                <p class="text-muted mb-3">
                                    <i class="bx bx-info-circle"></i> Catatan penyempurnaan terhadap sistematika
                                    dokumen perencanaan per Bab/Sub Bab
                                </p>

                                <!-- Form Tambah Sistematika -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-plus-circle"></i> Tambah Item Sistematika
                                        </h6>
                                        <form id="formSistematika">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Pilih Bab <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" id="master_bab_id" required>
                                                            <option value="">-- Pilih Bab --</option>
                                                            @foreach ($masterBabList as $bab)
                                                                <option value="{{ $bab->id }}" data-urutan="{{ $bab->urutan }}">{{ $bab->nama_bab }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sub Bab (Opsional)</label>
                                                        <input type="text" class="form-control" id="sub_bab"
                                                            placeholder="Contoh: 1.2 Dasar Hukum">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Penyempurnaan <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control tinymce-editor" id="catatan_penyempurnaan" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Tambah Item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Filter & Sort Sistematika -->
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded flex-wrap">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bx bx-filter-alt text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Filter:</label>
                                        <select class="form-select form-select-sm" id="filterUserSistematika" style="min-width:130px">
                                            <option value="">Semua User</option>
                                            @foreach ($sistematikUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 ms-auto">
                                        <i class="bx bx-sort-alt-2 text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Sort:</label>
                                        <select class="form-select form-select-sm" id="sortSistematika" style="min-width:110px">
                                            <option value="urutan">Urutan (Bab)</option>
                                            <option value="waktu">Waktu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- List Sistematika -->
                                <div id="listSistematika">
                                    @if ($hasilFasilitasi->hasilSistematika->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="20%">Bab / Sub Bab</th>
                                                        <th width="50%">Catatan Penyempurnaan</th>
                                                        <th width="15%">Oleh</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($hasilFasilitasi->hasilSistematika as $item)
                                                        @php
                                                            $catatanPenyempurnaan = is_string($item->catatan_penyempurnaan)
                                                                ? $item->catatan_penyempurnaan
                                                                : $item->catatan_penyempurnaan->render();
                                                        @endphp
                                                        <tr class="sistematika-item"
                                                            data-id="{{ $item->id }}"
                                                            data-bab-urutan="{{ $item->masterBab->urutan ?? 999 }}"
                                                            data-user-id="{{ $item->user_id }}"
                                                            data-created-at="{{ $item->created_at->timestamp }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td>
                                                                <strong>{{ $item->masterBab->nama_bab ?? '-' }}</strong>
                                                                @if ($item->sub_bab)
                                                                    <br><span
                                                                        class="text-muted">{{ $item->sub_bab }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="catatan-cell">{!! $catatanPenyempurnaan !!}</td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ $item->user->name ?? '-' }}<br>
                                                                    <span
                                                                        class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($isKoordinator || $item->user_id == Auth::id())
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-warning btn-edit-sistematika"
                                                                        data-id="{{ $item->id }}"
                                                                        data-sub-bab="{{ $item->sub_bab }}"
                                                                        data-catatan="{{ $catatanPenyempurnaan }}">
                                                                        <i class="bx bx-edit"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger btn-hapus-sistematika"
                                                                        data-id="{{ $item->id }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info" id="emptySistematika">
                                            <i class="bx bx-info-circle"></i> Belum ada item sistematika. Tambahkan item
                                            menggunakan form di atas.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab 2: Konsistensi & Keselarasan -->
                            <div class="tab-pane fade" id="form" role="tabpanel">
                                <p class="text-muted mb-3">
                                    <i class="bx bx-info-circle"></i> Catatan terkait konsistensi dan keselarasan dokumen perencanaan
                                </p>

                                <!-- Form Tambah Item -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-plus-circle"></i> Tambah Item</h6>
                                        <form id="formKonsistensi">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">Catatan <span class="text-danger">*</span></label>
                                                <textarea class="form-control tinymce-editor" id="catatan_form" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Tambah Item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Filter & Sort Konsistensi -->
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded flex-wrap">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bx bx-filter-alt text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Filter:</label>
                                        <select class="form-select form-select-sm" id="filterUserForm" style="min-width:130px">
                                            <option value="">Semua User</option>
                                            @foreach ($formUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 ms-auto">
                                        <i class="bx bx-sort-alt-2 text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Sort:</label>
                                        <select class="form-select form-select-sm" id="sortForm" style="min-width:110px">
                                            <option value="urutan">Urutan</option>
                                            <option value="waktu">Waktu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- List Konsistensi -->
                                <div id="listForm">
                                    @if ($hasilFasilitasi->hasilForm->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th>Catatan</th>
                                                        <th width="15%">Oleh</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tableForm">
                                                    @foreach ($hasilFasilitasi->hasilForm as $item)
                                                        <tr class="form-item"
                                                            data-id="{{ $item->id }}"
                                                            data-urutan="{{ $item->id }}"
                                                            data-user-id="{{ $item->user_id }}"
                                                            data-created-at="{{ $item->created_at->timestamp }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td class="catatan-cell">{!! $item->catatan !!}</td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ $item->user->name ?? '-' }}<br>
                                                                    <span class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($isKoordinator || $item->user_id == Auth::id())
                                                                    <button type="button" class="btn btn-sm btn-warning btn-edit-form" data-id="{{ $item->id }}" data-catatan="{{ $item->catatan }}">
                                                                        <i class="bx bx-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-form" data-id="{{ $item->id }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info" id="emptyForm">
                                            <i class="bx bx-info-circle"></i> Belum ada item. Tambahkan menggunakan form di atas.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab 3: Urusan Pemerintahan -->
                            <div class="tab-pane fade" id="urusan" role="tabpanel">
                                <p class="text-muted mb-3">
                                    <i class="bx bx-info-circle"></i> Catatan masukan/saran terhadap urusan
                                    pemerintahan konkuren yang diselenggarakan daerah
                                </p>

                                <!-- Form Tambah Urusan -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-plus-circle"></i> Tambah Item Urusan</h6>
                                        <form id="formUrusan">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Pilih Urusan <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" id="master_urusan_id" required>
                                                            <option value="">-- Pilih Urusan --</option>
                                                            @foreach ($masterUrusanList as $urusan)
                                                                <option value="{{ $urusan->id }}" data-urutan="{{ $urusan->urutan }}">{{ $urusan->urutan }}. {{ $urusan->nama_urusan }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Masukan / Saran <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control tinymce-editor" id="catatan_masukan" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Tambah Item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Filter & Sort Urusan -->
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded flex-wrap">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bx bx-filter-alt text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Filter:</label>
                                        <select class="form-select form-select-sm" id="filterUserUrusan" style="min-width:130px">
                                            <option value="">Semua User</option>
                                            @foreach ($urusanUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 ms-auto">
                                        <i class="bx bx-sort-alt-2 text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Sort:</label>
                                        <select class="form-select form-select-sm" id="sortUrusan" style="min-width:110px">
                                            <option value="urutan">Urutan (Urusan)</option>
                                            <option value="waktu">Waktu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- List Urusan -->
                                <div id="listUrusan">
                                    @if ($hasilFasilitasi->hasilUrusan->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="25%">Urusan Pemerintahan</th>
                                                        <th width="45%">Catatan Masukan / Saran</th>
                                                        <th width="15%">Oleh</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($hasilFasilitasi->hasilUrusan as $item)
                                                        @php
                                                            $catatanMasukan = is_string($item->catatan_masukan)
                                                                ? $item->catatan_masukan
                                                                : $item->catatan_masukan->render();
                                                        @endphp
                                                        <tr class="urusan-item"
                                                            data-id="{{ $item->id }}"
                                                            data-urusan-urutan="{{ $item->masterUrusan->urutan ?? 999 }}"
                                                            data-user-id="{{ $item->user_id }}"
                                                            data-created-at="{{ $item->created_at->timestamp }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td><strong>{{ $item->masterUrusan->urutan }}.
                                                                    {{ $item->masterUrusan->nama_urusan }}</strong></td>
                                                            <td class="catatan-cell">{!! $catatanMasukan !!}</td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ $item->user->name ?? '-' }}<br>
                                                                    <span
                                                                        class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($isKoordinator || $item->user_id == Auth::id())
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-warning btn-edit-urusan"
                                                                        data-id="{{ $item->id }}"
                                                                        data-catatan="{{ $catatanMasukan }}">
                                                                        <i class="bx bx-edit"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger btn-hapus-urusan"
                                                                        data-id="{{ $item->id }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info" id="emptyUrusan">
                                            <i class="bx bx-info-circle"></i> Belum ada item urusan. Tambahkan item
                                            menggunakan form di atas.
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Tab 4: Rekomendasi -->
                            <div class="tab-pane fade" id="rekomendasi" role="tabpanel">
                                <p class="text-muted mb-3">
                                    <i class="bx bx-info-circle"></i> Catatan rekomendasi hasil fasilitasi / evaluasi
                                </p>

                                <!-- Form Tambah Rekomendasi -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-plus-circle"></i> Tambah Item</h6>
                                        <form id="formRekomendasi">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">Catatan <span class="text-danger">*</span></label>
                                                <textarea class="form-control tinymce-editor" id="catatan_rekomendasi" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Tambah Item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Filter & Sort Rekomendasi -->
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded flex-wrap">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bx bx-filter-alt text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Filter:</label>
                                        <select class="form-select form-select-sm" id="filterUserRekomendasi" style="min-width:130px">
                                            <option value="">Semua User</option>
                                            @foreach ($rekomendasiUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 ms-auto">
                                        <i class="bx bx-sort-alt-2 text-muted small"></i>
                                        <label class="form-label mb-0 small fw-semibold">Sort:</label>
                                        <select class="form-select form-select-sm" id="sortRekomendasi" style="min-width:110px">
                                            <option value="urutan">Urutan</option>
                                            <option value="waktu">Waktu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- List Rekomendasi -->
                                <div id="listRekomendasi">
                                    @if ($hasilFasilitasi->hasilRekomendasi->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th>Catatan</th>
                                                        <th width="15%">Oleh</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tableRekomendasi">
                                                    @foreach ($hasilFasilitasi->hasilRekomendasi as $item)
                                                        <tr class="rekomendasi-item"
                                                            data-id="{{ $item->id }}"
                                                            data-urutan="{{ $item->id }}"
                                                            data-user-id="{{ $item->user_id }}"
                                                            data-created-at="{{ $item->created_at->timestamp }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td class="catatan-cell">{!! $item->catatan !!}</td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ $item->user->name ?? '-' }}<br>
                                                                    <span class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($isKoordinator || $item->user_id == Auth::id())
                                                                    <button type="button" class="btn btn-sm btn-warning btn-edit-rekomendasi" data-id="{{ $item->id }}" data-catatan="{{ $item->catatan }}">
                                                                        <i class="bx bx-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-rekomendasi" data-id="{{ $item->id }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info" id="emptyRekomendasi">
                                            <i class="bx bx-info-circle"></i> Belum ada item. Tambahkan menggunakan form di atas.
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Sistematika -->
    <div class="modal fade" id="editSistematikaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Item Sistematika</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editSistematikaId">
                    <div class="mb-3">
                        <label class="form-label">Sub Bab (Opsional)</label>
                        <input type="text" class="form-control" id="edit_sub_bab"
                            placeholder="Contoh: 1.2 Dasar Hukum">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Penyempurnaan <span class="text-danger">*</span></label>
                        <textarea class="form-control tinymce-editor" id="edit_catatan_penyempurnaan" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEditSistematika">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Urusan -->
    <div class="modal fade" id="editUrusanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Item Urusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editUrusanId">
                    <div class="mb-3">
                        <label class="form-label">Catatan Masukan / Saran <span class="text-danger">*</span></label>
                        <textarea class="form-control tinymce-editor" id="edit_catatan_masukan" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEditUrusan">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Konsistensi & Keselarasan -->
    <div class="modal fade" id="editFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Konsistensi & Keselarasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editFormId">
                    <div class="mb-3">
                        <label class="form-label">Catatan <span class="text-danger">*</span></label>
                        <textarea class="form-control tinymce-editor" id="edit_catatan_form" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEditForm">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rekomendasi -->
    <div class="modal fade" id="editRekomendasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Rekomendasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editRekomendasiId">
                    <div class="mb-3">
                        <label class="form-label">Catatan <span class="text-danger">*</span></label>
                        <textarea class="form-control tinymce-editor" id="edit_catatan_rekomendasi" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEditRekomendasi">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
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
    <script src="https://cdn.tiny.cloud/1/{{ env('TINY_MCE_API_KEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin">
    </script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '.tinymce-editor',
            height: 220,
            menubar: false,
            plugins: ['advlist', 'autolink', 'lists', 'searchreplace', 'wordcount'],
            toolbar: 'undo redo | bold italic underline strikethrough | ' +
                     'bullist numlist outdent indent | removeformat',
            // Font dan ukuran disesuaikan dengan dokumen Word/PDF yang dihasilkan
            content_style: 'body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5; margin: 8px; }' +
                           'p { margin: 0 0 4px 0; } ul, ol { margin: 2px 0; padding-left: 20px; }',
            branding: false,
            promotion: false,
            statusbar: false,

            // Hanya izinkan elemen yang dirender dengan baik oleh PhpWord dan DomPDF
            valid_elements: 'p,br,strong/b,em/i,u,s/strike/del,ul,ol,li,span[style],sub,sup',
            valid_styles: {
                '*': 'font-weight,font-style,text-decoration'
            },

            paste_as_text: false,
            paste_webkit_styles: 'font-weight font-style text-decoration',
            paste_retain_style_properties: 'font-weight font-style text-decoration',

            setup: function(editor) {
                editor.on('init', function() {
                    console.log('TinyMCE initialized for: ' + editor.id);
                });
            }
        });

        const permohonanId = {{ $permohonan->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const isKoordinator = {{ $isKoordinator ? 'true' : 'false' }};
        const currentUserId = {{ Auth::id() }};

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

        // ============================================================
        // FILTER & SORT
        // ============================================================
        const tabConfigs = {
            sistematika: {
                listId: 'listSistematika',
                itemClass: 'sistematika-item',
                filterSelectId: 'filterUserSistematika',
                sortSelectId: 'sortSistematika',
                urutanAttr: 'babUrutan',
            },
            form: {
                listId: 'listForm',
                itemClass: 'form-item',
                filterSelectId: 'filterUserForm',
                sortSelectId: 'sortForm',
                urutanAttr: 'urutan',
            },
            urusan: {
                listId: 'listUrusan',
                itemClass: 'urusan-item',
                filterSelectId: 'filterUserUrusan',
                sortSelectId: 'sortUrusan',
                urutanAttr: 'urusanUrutan',
            },
            rekomendasi: {
                listId: 'listRekomendasi',
                itemClass: 'rekomendasi-item',
                filterSelectId: 'filterUserRekomendasi',
                sortSelectId: 'sortRekomendasi',
                urutanAttr: 'urutan',
            },
        };

        function applyFilterSort(config) {
            const listEl = document.getElementById(config.listId);
            if (!listEl) return;
            const tbody = listEl.querySelector('tbody');
            if (!tbody) return;

            const filterValue = document.getElementById(config.filterSelectId)?.value || '';
            const sortValue = document.getElementById(config.sortSelectId)?.value || 'urutan';

            const rows = Array.from(tbody.querySelectorAll(`tr.${config.itemClass}`));
            if (!rows.length) return;

            rows.sort((a, b) => {
                if (sortValue === 'waktu') {
                    return parseInt(a.dataset.createdAt || 0) - parseInt(b.dataset.createdAt || 0);
                }
                return parseInt(a.dataset[config.urutanAttr] || 999) - parseInt(b.dataset[config.urutanAttr] || 999);
            });

            rows.forEach(row => tbody.appendChild(row));

            let num = 1;
            rows.forEach(row => {
                const match = !filterValue || row.dataset.userId === filterValue;
                row.style.display = match ? '' : 'none';
                if (match) row.querySelector('td:first-child').textContent = num++;
            });
        }

        Object.values(tabConfigs).forEach(config => {
            ['filterSelectId', 'sortSelectId'].forEach(key => {
                const el = document.getElementById(config[key]);
                if (el) el.addEventListener('change', () => applyFilterSort(config));
            });
        });

        // Submit Form Sistematika
        document.getElementById('formSistematika').addEventListener('submit', async function(e) {
            e.preventDefault();

            const masterBabId = document.getElementById('master_bab_id').value;
            const subBab = document.getElementById('sub_bab').value;

            // Get content from TinyMCE
            const catatanPenyempurnaan = tinymce.get('catatan_penyempurnaan').getContent();

            // Ambil nama bab dari dropdown
            const selectElement = document.getElementById('master_bab_id');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const namaBab = selectedOption.text;

            try {
                const response = await fetch(`/hasil-fasilitasi/${permohonanId}/sistematika`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        master_bab_id: masterBabId,
                        sub_bab: subBab,
                        catatan_penyempurnaan: catatanPenyempurnaan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reset form
                    document.getElementById('formSistematika').reset();
                    tinymce.get('catatan_penyempurnaan').setContent('');

                    // Tambahkan ke list
                    const listSistematika = document.getElementById('listSistematika');
                    const emptyAlert = document.getElementById('emptySistematika');

                    if (emptyAlert) {
                        // Jika belum ada tabel, buat tabel baru
                        emptyAlert.remove();
                        listSistematika.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="20%">Bab / Sub Bab</th>
                                            <th width="50%">Catatan Penyempurnaan</th>
                                            <th width="15%">Oleh</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableSistematika"></tbody>
                                </table>
                            </div>
                        `;
                    }

                    const tbody = document.getElementById('tableSistematika') || listSistematika.querySelector(
                        'tbody');
                    const rowCount = tbody.querySelectorAll('tr').length + 1;

                    // Format tampilan bab/sub bab
                    let displayBabSubBab = `<strong>${namaBab}</strong>`;
                    if (subBab) {
                        displayBabSubBab += `<br><span class="text-muted">${subBab}</span>`;
                    }

                    // Check if user can manage (koordinator or owner)
                    const canManage = isKoordinator || data.data.user_id === currentUserId;
                    const actionButtons = canManage ?
                        `<button type="button" class="btn btn-sm btn-warning btn-edit-sistematika" data-id="${data.data.id}" data-sub-bab="${subBab}" data-catatan="${encodeURIComponent(data.data.catatan_penyempurnaan)}">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-sistematika" data-id="${data.data.id}">
                                <i class="bx bx-trash"></i>
                            </button>` :
                        `<span class="text-muted small">-</span>`;

                    const babUrutan = selectedOption.dataset.urutan || 999;
                    const createdAtTs = data.data.created_at_ts || Math.floor(Date.now() / 1000);
                    const newRow = `
                        <tr class="sistematika-item" data-id="${data.data.id}"
                            data-bab-urutan="${babUrutan}"
                            data-user-id="${data.data.user_id}"
                            data-created-at="${createdAtTs}">
                            <td class="text-center">${rowCount}</td>
                            <td>${displayBabSubBab}</td>
                            <td class="catatan-cell">${data.data.catatan_penyempurnaan}</td>
                            <td><small class="text-muted">${data.data.user ? data.data.user.name : '-'}<br><span class="text-secondary">${data.data.created_at}</span></small></td>
                            <td class="text-center">
                                ${actionButtons}
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', newRow);
                    applyFilterSort(tabConfigs.sistematika);

                    showToast('success', data.message);
                } else {
                    showToast('error', data.error || 'Gagal menambahkan item');
                }
            } catch (error) {
                showToast('error', 'Terjadi kesalahan: ' + error.message);
            }
        });

        // Submit Form Urusan
        document.getElementById('formUrusan').addEventListener('submit', async function(e) {
            e.preventDefault();

            const masterUrusanId = document.getElementById('master_urusan_id').value;

            // Get content from TinyMCE
            const catatanMasukan = tinymce.get('catatan_masukan').getContent();

            const selectElement = document.getElementById('master_urusan_id');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const namaUrusan = selectedOption.text;

            try {
                const response = await fetch(`/hasil-fasilitasi/${permohonanId}/urusan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        master_urusan_id: masterUrusanId,
                        catatan_masukan: catatanMasukan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reset form
                    document.getElementById('formUrusan').reset();
                    tinymce.get('catatan_masukan').setContent('');

                    // Tambahkan ke list
                    const listUrusan = document.getElementById('listUrusan');
                    const emptyAlert = document.getElementById('emptyUrusan');

                    if (emptyAlert) {
                        // Jika belum ada tabel, buat tabel baru
                        emptyAlert.remove();
                        listUrusan.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="25%">Urusan Pemerintahan</th>
                                            <th width="45%">Catatan Masukan / Saran</th>
                                            <th width="15%">Oleh</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableUrusan"></tbody>
                                </table>
                            </div>
                        `;
                    }

                    const tbody = document.getElementById('tableUrusan') || listUrusan.querySelector('tbody');
                    const rowCount = tbody.querySelectorAll('tr').length + 1;

                    // Check if user can manage (koordinator or owner)
                    const canManage = isKoordinator || data.data.user_id === currentUserId;
                    const actionButtons = canManage ?
                        `<button type="button" class="btn btn-sm btn-warning btn-edit-urusan" data-id="${data.data.id}" data-catatan="${encodeURIComponent(data.data.catatan_masukan)}">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-urusan" data-id="${data.data.id}">
                                <i class="bx bx-trash"></i>
                            </button>` :
                        `<span class="text-muted small">-</span>`;

                    const urusanUrutan = selectedOption.dataset.urutan || 999;
                    const urusanCreatedAtTs = data.data.created_at_ts || Math.floor(Date.now() / 1000);
                    const newRow = `
                        <tr class="urusan-item" data-id="${data.data.id}"
                            data-urusan-urutan="${urusanUrutan}"
                            data-user-id="${data.data.user_id}"
                            data-created-at="${urusanCreatedAtTs}">
                            <td class="text-center">${rowCount}</td>
                            <td><strong>${namaUrusan}</strong></td>
                            <td class="catatan-cell">${data.data.catatan_masukan}</td>
                            <td><small class="text-muted">${data.data.user ? data.data.user.name : '-'}<br><span class="text-secondary">${data.data.created_at}</span></small></td>
                            <td class="text-center">
                                ${actionButtons}
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', newRow);
                    applyFilterSort(tabConfigs.urusan);

                    showToast('success', data.message);
                } else {
                    showToast('error', data.error || 'Gagal menambahkan item');
                }
            } catch (error) {
                showToast('error', 'Terjadi kesalahan: ' + error.message);
            }
        });

        // Event delegation untuk tombol hapus
        document.addEventListener('click', async function(e) {
            // Edit Sistematika
            if (e.target.closest('.btn-edit-sistematika')) {
                const btn = e.target.closest('.btn-edit-sistematika');
                document.getElementById('editSistematikaId').value = btn.dataset.id;
                document.getElementById('edit_sub_bab').value = btn.dataset.subBab || '';
                tinymce.get('edit_catatan_penyempurnaan').setContent(decodeURIComponent(btn.dataset.catatan));
                new bootstrap.Modal(document.getElementById('editSistematikaModal')).show();
            }

            // Edit Urusan
            if (e.target.closest('.btn-edit-urusan')) {
                const btn = e.target.closest('.btn-edit-urusan');
                document.getElementById('editUrusanId').value = btn.dataset.id;
                tinymce.get('edit_catatan_masukan').setContent(decodeURIComponent(btn.dataset.catatan));
                new bootstrap.Modal(document.getElementById('editUrusanModal')).show();
            }

            // Hapus Sistematika
            if (e.target.closest('.btn-hapus-sistematika')) {
                const btn = e.target.closest('.btn-hapus-sistematika');
                const id = btn.dataset.id;

                if (confirm('Hapus item sistematika ini?')) {
                    try {
                        const response = await fetch(`/hasil-fasilitasi/${permohonanId}/sistematika/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            document.querySelector(`.sistematika-item[data-id="${id}"]`).remove();
                            const listSistematika = document.getElementById('listSistematika');
                            if (!listSistematika.querySelector('.sistematika-item')) {
                                listSistematika.innerHTML =
                                    '<div class="alert alert-info" id="emptySistematika"><i class="bx bx-info-circle"></i> Belum ada item sistematika.</div>';
                            } else { applyFilterSort(tabConfigs.sistematika); }
                            showToast('success', data.message);
                        } else {
                            showToast('error', data.error || 'Gagal menghapus item');
                        }
                    } catch (error) {
                        showToast('error', 'Terjadi kesalahan: ' + error.message);
                    }
                }
            }

            // Hapus Urusan
            if (e.target.closest('.btn-hapus-urusan')) {
                const btn = e.target.closest('.btn-hapus-urusan');
                const id = btn.dataset.id;

                if (confirm('Hapus item urusan ini?')) {
                    try {
                        const response = await fetch(`/hasil-fasilitasi/${permohonanId}/urusan/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            document.querySelector(`.urusan-item[data-id="${id}"]`).remove();
                            const listUrusan = document.getElementById('listUrusan');
                            if (!listUrusan.querySelector('.urusan-item')) {
                                listUrusan.innerHTML =
                                    '<div class="alert alert-info" id="emptyUrusan"><i class="bx bx-info-circle"></i> Belum ada item urusan.</div>';
                            } else { applyFilterSort(tabConfigs.urusan); }
                            showToast('success', data.message);
                        } else {
                            showToast('error', data.error || 'Gagal menghapus item');
                        }
                    } catch (error) {
                        showToast('error', 'Terjadi kesalahan: ' + error.message);
                    }
                }
            }
        });

        // ============================================================
        // KONSISTENSI & KESELARASAN (FORM) HANDLERS
        // ============================================================

        function buildFormTable() {
            return `<div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th width="5%">No</th><th>Catatan</th><th width="15%">Oleh</th><th width="10%">Aksi</th></tr>
                    </thead>
                    <tbody id="tableForm"></tbody>
                </table>
            </div>`;
        }

        function buildRekomendasiTable() {
            return `<div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th width="5%">No</th><th>Catatan</th><th width="15%">Oleh</th><th width="10%">Aksi</th></tr>
                    </thead>
                    <tbody id="tableRekomendasi"></tbody>
                </table>
            </div>`;
        }

        function buildItemRow(type, data) {
            const canManage = isKoordinator || data.user_id === currentUserId;
            const editBtn = canManage ? `<button type="button" class="btn btn-sm btn-warning btn-edit-${type}" data-id="${data.id}" data-catatan="${encodeURIComponent(data.catatan)}"><i class="bx bx-edit"></i></button>` : '';
            const deleteBtn = canManage ? `<button type="button" class="btn btn-sm btn-danger btn-hapus-${type}" data-id="${data.id}"><i class="bx bx-trash"></i></button>` : '<span class="text-muted small">-</span>';
            const createdAtTs = data.created_at_ts || Math.floor(Date.now() / 1000);
            return `<tr class="${type}-item" data-id="${data.id}" data-urutan="${data.id}" data-user-id="${data.user_id}" data-created-at="${createdAtTs}">
                <td class="text-center"></td>
                <td class="catatan-cell">${data.catatan}</td>
                <td><small class="text-muted">${data.user ? data.user.name : '-'}<br><span class="text-secondary">${data.created_at}</span></small></td>
                <td class="text-center">${editBtn} ${deleteBtn}</td>
            </tr>`;
        }

        function renumberRows(tbodyId) {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) return;
            tbody.querySelectorAll('tr').forEach((row, i) => {
                row.querySelector('td:first-child').textContent = i + 1;
            });
        }

        // Submit Form Konsistensi
        document.getElementById('formKonsistensi').addEventListener('submit', async function(e) {
            e.preventDefault();
            const catatan = tinymce.get('catatan_form').getContent();
            if (!catatan.trim()) { showToast('error', 'Catatan tidak boleh kosong'); return; }
            try {
                const res = await fetch(`/hasil-fasilitasi/${permohonanId}/form`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ catatan })
                });
                const data = await res.json();
                if (data.success) {
                    tinymce.get('catatan_form').setContent('');
                    const listEl = document.getElementById('listForm');
                    const emptyEl = document.getElementById('emptyForm');
                    if (emptyEl) { emptyEl.remove(); listEl.innerHTML = buildFormTable(); }
                    const tbody = document.getElementById('tableForm') || listEl.querySelector('tbody');
                    tbody.insertAdjacentHTML('beforeend', buildItemRow('form', data.data));
                    applyFilterSort(tabConfigs.form);
                    showToast('success', data.message);
                } else { showToast('error', data.error || 'Gagal menambahkan item'); }
            } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
        });

        // Submit Form Rekomendasi
        document.getElementById('formRekomendasi').addEventListener('submit', async function(e) {
            e.preventDefault();
            const catatan = tinymce.get('catatan_rekomendasi').getContent();
            if (!catatan.trim()) { showToast('error', 'Catatan tidak boleh kosong'); return; }
            try {
                const res = await fetch(`/hasil-fasilitasi/${permohonanId}/rekomendasi`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ catatan })
                });
                const data = await res.json();
                if (data.success) {
                    tinymce.get('catatan_rekomendasi').setContent('');
                    const listEl = document.getElementById('listRekomendasi');
                    const emptyEl = document.getElementById('emptyRekomendasi');
                    if (emptyEl) { emptyEl.remove(); listEl.innerHTML = buildRekomendasiTable(); }
                    const tbody = document.getElementById('tableRekomendasi') || listEl.querySelector('tbody');
                    tbody.insertAdjacentHTML('beforeend', buildItemRow('rekomendasi', data.data));
                    applyFilterSort(tabConfigs.rekomendasi);
                    showToast('success', data.message);
                } else { showToast('error', data.error || 'Gagal menambahkan item'); }
            } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
        });

        // Edit & Delete — event delegation
        document.addEventListener('click', async function(e) {
            // Edit Form
            if (e.target.closest('.btn-edit-form')) {
                const btn = e.target.closest('.btn-edit-form');
                document.getElementById('editFormId').value = btn.dataset.id;
                tinymce.get('edit_catatan_form').setContent(decodeURIComponent(btn.dataset.catatan));
                new bootstrap.Modal(document.getElementById('editFormModal')).show();
            }
            // Hapus Form
            if (e.target.closest('.btn-hapus-form')) {
                const btn = e.target.closest('.btn-hapus-form');
                if (!confirm('Hapus item ini?')) return;
                try {
                    const res = await fetch(`/hasil-fasilitasi/${permohonanId}/form/${btn.dataset.id}`, {
                        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken }
                    });
                    const data = await res.json();
                    if (data.success) {
                        document.querySelector(`.form-item[data-id="${btn.dataset.id}"]`).remove();
                        const listEl = document.getElementById('listForm');
                        if (!listEl.querySelector('.form-item')) {
                            listEl.innerHTML = '<div class="alert alert-info" id="emptyForm"><i class="bx bx-info-circle"></i> Belum ada item.</div>';
                        } else { applyFilterSort(tabConfigs.form); }
                        showToast('success', data.message);
                    } else { showToast('error', data.error || 'Gagal menghapus'); }
                } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
            }
            // Edit Rekomendasi
            if (e.target.closest('.btn-edit-rekomendasi')) {
                const btn = e.target.closest('.btn-edit-rekomendasi');
                document.getElementById('editRekomendasiId').value = btn.dataset.id;
                tinymce.get('edit_catatan_rekomendasi').setContent(decodeURIComponent(btn.dataset.catatan));
                new bootstrap.Modal(document.getElementById('editRekomendasiModal')).show();
            }
            // Hapus Rekomendasi
            if (e.target.closest('.btn-hapus-rekomendasi')) {
                const btn = e.target.closest('.btn-hapus-rekomendasi');
                if (!confirm('Hapus item ini?')) return;
                try {
                    const res = await fetch(`/hasil-fasilitasi/${permohonanId}/rekomendasi/${btn.dataset.id}`, {
                        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken }
                    });
                    const data = await res.json();
                    if (data.success) {
                        document.querySelector(`.rekomendasi-item[data-id="${btn.dataset.id}"]`).remove();
                        const listEl = document.getElementById('listRekomendasi');
                        if (!listEl.querySelector('.rekomendasi-item')) {
                            listEl.innerHTML = '<div class="alert alert-info" id="emptyRekomendasi"><i class="bx bx-info-circle"></i> Belum ada item.</div>';
                        } else { applyFilterSort(tabConfigs.rekomendasi); }
                        showToast('success', data.message);
                    } else { showToast('error', data.error || 'Gagal menghapus'); }
                } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
            }
        });

        // Save Edit Sistematika
        document.getElementById('btnSaveEditSistematika').addEventListener('click', async function() {
            const id = document.getElementById('editSistematikaId').value;
            const subBab = document.getElementById('edit_sub_bab').value;
            const catatan = tinymce.get('edit_catatan_penyempurnaan').getContent();
            if (!catatan.trim()) { showToast('error', 'Catatan tidak boleh kosong'); return; }
            try {
                const res = await fetch(`/hasil-fasilitasi/${permohonanId}/sistematika/${id}`, {
                    method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ sub_bab: subBab, catatan_penyempurnaan: catatan })
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`.sistematika-item[data-id="${id}"]`);
                    row.querySelector('.catatan-cell').innerHTML = catatan;
                    const editBtn = row.querySelector('.btn-edit-sistematika');
                    editBtn.dataset.catatan = encodeURIComponent(catatan);
                    editBtn.dataset.subBab = subBab;

                    // Update tampilan sub bab
                    const babSubBabCell = row.children[1];
                    const namaBabEl = babSubBabCell.querySelector('strong');
                    babSubBabCell.innerHTML = '';
                    babSubBabCell.appendChild(namaBabEl);
                    if (subBab) {
                        babSubBabCell.insertAdjacentHTML('beforeend', `<br><span class="text-muted">${subBab}</span>`);
                    }

                    bootstrap.Modal.getInstance(document.getElementById('editSistematikaModal')).hide();
                    showToast('success', data.message);
                } else { showToast('error', data.error || 'Gagal menyimpan'); }
            } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
        });

        // Save Edit Urusan
        document.getElementById('btnSaveEditUrusan').addEventListener('click', async function() {
            const id = document.getElementById('editUrusanId').value;
            const catatan = tinymce.get('edit_catatan_masukan').getContent();
            if (!catatan.trim()) { showToast('error', 'Catatan tidak boleh kosong'); return; }
            try {
                const res = await fetch(`/hasil-fasilitasi/${permohonanId}/urusan/${id}`, {
                    method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ catatan_masukan: catatan })
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`.urusan-item[data-id="${id}"]`);
                    row.querySelector('.catatan-cell').innerHTML = catatan;
                    row.querySelector('.btn-edit-urusan').dataset.catatan = encodeURIComponent(catatan);
                    bootstrap.Modal.getInstance(document.getElementById('editUrusanModal')).hide();
                    showToast('success', data.message);
                } else { showToast('error', data.error || 'Gagal menyimpan'); }
            } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
        });

        // Save Edit Form
        document.getElementById('btnSaveEditForm').addEventListener('click', async function() {
            const id = document.getElementById('editFormId').value;
            const catatan = tinymce.get('edit_catatan_form').getContent();
            if (!catatan.trim()) { showToast('error', 'Catatan tidak boleh kosong'); return; }
            try {
                const res = await fetch(`/hasil-fasilitasi/${permohonanId}/form/${id}`, {
                    method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ catatan })
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`.form-item[data-id="${id}"]`);
                    row.querySelector('.catatan-cell').innerHTML = catatan;
                    row.querySelector('.btn-edit-form').dataset.catatan = encodeURIComponent(catatan);
                    bootstrap.Modal.getInstance(document.getElementById('editFormModal')).hide();
                    showToast('success', data.message);
                } else { showToast('error', data.error || 'Gagal menyimpan'); }
            } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
        });

        // Save Edit Rekomendasi
        document.getElementById('btnSaveEditRekomendasi').addEventListener('click', async function() {
            const id = document.getElementById('editRekomendasiId').value;
            const catatan = tinymce.get('edit_catatan_rekomendasi').getContent();
            if (!catatan.trim()) { showToast('error', 'Catatan tidak boleh kosong'); return; }
            try {
                const res = await fetch(`/hasil-fasilitasi/${permohonanId}/rekomendasi/${id}`, {
                    method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ catatan })
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`.rekomendasi-item[data-id="${id}"]`);
                    row.querySelector('.catatan-cell').innerHTML = catatan;
                    row.querySelector('.btn-edit-rekomendasi').dataset.catatan = encodeURIComponent(catatan);
                    bootstrap.Modal.getInstance(document.getElementById('editRekomendasiModal')).hide();
                    showToast('success', data.message);
                } else { showToast('error', data.error || 'Gagal menyimpan'); }
            } catch (err) { showToast('error', 'Terjadi kesalahan: ' + err.message); }
        });

        // Show toast notification
        function showToast(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible position-fixed top-0 end-0 m-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.style.maxWidth = '400px';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
@endpush
