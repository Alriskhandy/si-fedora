@extends('layouts.app')

@section('title', 'Tahapan Hasil Fasilitasi / Evaluasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Hasil Fasilitasi / Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail</a></li>
                        <li class="breadcrumb-item active">Tahapan Hasil</li>
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

        @php
            // Get tahapan Hasil Fasilitasi untuk cek deadline
            $masterTahapanHasil = \App\Models\MasterTahapan::where(
                'nama_tahapan',
                'Hasil Fasilitasi / Evaluasi',
            )->first();
            $tahapanHasil = null;
            $batasWaktu = null;
            $isOverdue = false;

            if ($masterTahapanHasil) {
                $tahapanHasil = \App\Models\PermohonanTahapan::where('permohonan_id', $permohonan->id)
                    ->where('tahapan_id', $masterTahapanHasil->id)
                    ->first();

                // Cek deadline dari kolom deadline di permohonan_tahapan
                if ($tahapanHasil && $tahapanHasil->deadline) {
                    $batasWaktu = \Carbon\Carbon::parse($tahapanHasil->deadline);
                    $isOverdue = \Carbon\Carbon::now()->gt($batasWaktu);
                }
            }

            // Cek apakah user adalah fasilitator/koordinator yang di-assign
            $isFasilitator =
                auth()
                    ->user()
                    ->hasAnyRole(['fasilitator', 'koordinator']) &&
                \App\Models\UserKabkotaAssignment::where('user_id', auth()->id())
                    ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
                    ->where('tahun', $permohonan->tahun)
                    ->where('is_active', true)
                    ->exists();
        @endphp

        <!-- Info Batas Waktu (hanya untuk admin & tim fedora) -->
        @if (
            $tahapanHasil &&
                $batasWaktu &&
                (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']) ||
                    $isFasilitator))
            <div class="card border-0 shadow-sm mb-4 {{ $isOverdue ? 'border-danger' : 'border-warning' }}"
                style="border-left: 4px solid {{ $isOverdue ? '#dc3545' : '#ffc107' }} !important;">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <h6 class="mb-2 fw-bold">
                                Batas Waktu Input Hasil Fasilitasi
                            </h6>
                            <div class="mb-1">
                                @if ($isOverdue)
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class='bx bx-error-circle me-1'></i>
                                        Melebihi Batas Waktu
                                    </span>
                                    <span class="text-danger fw-bold ms-2">
                                        {{ $batasWaktu->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class='bx bx-time me-1'></i>
                                        Aktif
                                    </span>
                                    <span class="fw-bold ms-2">
                                        {{ $batasWaktu->format('d F Y, H:i') }}
                                    </span>
                                    <span class="text-muted small">
                                        ({{ $batasWaktu->diffForHumans() }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                @if (
                                    $isFasilitator ||
                                        auth()->user()->hasAnyRole(['admin_peran', 'superadmin', 'kaban']))
                                    @if (!$permohonan->hasilFasilitasi || $permohonan->hasilFasilitasi->status_validasi !== 'tervalidasi')
                                        <a href="{{ route('hasil-fasilitasi.show', $permohonan) }}"
                                            class="btn btn-primary shadow-sm">
                                            <i
                                                class='bx {{ !$permohonan->hasilFasilitasi ? 'bx-plus-circle' : 'bx-edit' }} me-1'></i>
                                            {{ !$permohonan->hasilFasilitasi ? 'Input' : 'Lihat' }}
                                        </a>
                                    @endif
                                @endif
                                @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
                                    <button type="button" class="btn btn-warning shadow-sm" data-bs-toggle="modal"
                                        data-bs-target="#perpanjanganWaktuModal">
                                        <i class='bx bx-calendar-plus me-2'></i>Perpanjang Waktu
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Penyampaian Hasil Fasilitasi / Evaluasi Disetujui Kepala Badan -->
        @if (
            $permohonan->hasilFasilitasi &&
                $permohonan->hasilFasilitasi->draft_final_file &&
                $permohonan->hasilFasilitasi->status_draft === 'disetujui_kaban')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                    <h5 class="fw-bold mb-1">
                        Penyampaian Hasil Fasilitasi / Evaluasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-0">
                        <!-- Informasi Permohonan -->
                        <div class="col-md-4 border-end">
                            <div class="p-4">
                                <h6 class="mb-4 text-uppercase text-muted small fw-bold">
                                    <i class='bx bx-info-circle me-1'></i>Informasi Permohonan
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
                                <div class="mb-4">
                                    <label class="text-muted small d-block mb-2">Tanggal Disetujui</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-calendar-check text-success me-1'></i>
                                        {{ $permohonan->hasilFasilitasi->tanggal_disetujui_kaban ? \Carbon\Carbon::parse($permohonan->hasilFasilitasi->tanggal_disetujui_kaban)->format('d F Y, H:i') : '-' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="text-muted small d-block mb-2">Status</label>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class='bx bx-check-shield me-1'></i>Selesai
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- PDF Preview -->
                        <div class="col-md-8">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 fw-bold">
                                        <i class='bx bx-file-pdf me-1 text-danger'></i>Preview Dokumen
                                    </h6>
                                    <a href="{{ route('hasil-fasilitasi.download-draft-final', $permohonan) }}"
                                        class="btn btn-primary btn-sm shadow-sm" target="_blank">
                                        <i class='bx bx-download me-1'></i>Download PDF
                                    </a>
                                </div>

                                <div class="ratio ratio-16x9 border rounded" style="min-height: 600px;">
                                    <iframe
                                        src="{{ asset('storage/' . $permohonan->hasilFasilitasi->draft_final_file) }}#toolbar=1&view=FitH"
                                        type="application/pdf" width="100%" height="600px" style="border: none;">
                                        <p class="text-center py-5">
                                            Browser Anda tidak mendukung preview PDF.
                                            <a href="{{ route('hasil-fasilitasi.download-draft-final', $permohonan) }}"
                                                class="btn btn-primary" target="_blank">
                                                <i class='bx bx-download me-1'></i>Download PDF
                                            </a>
                                        </p>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Perpanjangan Waktu -->
        @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
            <div class="modal fade" id="perpanjanganWaktuModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-warning bg-opacity-10 border-bottom-0">
                            <div>
                                <h5 class="modal-title fw-bold">
                                    <i class='bx bx-calendar-plus me-2 text-warning'></i>Perpanjang Batas Waktu
                                </h5>
                                <p class="mb-0 small text-muted">Perpanjang deadline untuk input hasil fasilitasi</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="{{ route('permohonan.tahapan.update-deadline', $permohonan) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="tahapan" value="Hasil Fasilitasi / Evaluasi">

                            <div class="modal-body p-4">
                                <div class="alert alert-info border-0 shadow-sm mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class='bx bx-info-circle' style="font-size: 24px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted mb-1">Batas waktu saat ini</div>
                                            <strong class="d-block">
                                                @if ($batasWaktu)
                                                    {{ $batasWaktu->format('d F Y, H:i') }}
                                                    <span
                                                        class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning text-dark' }} ms-2">
                                                        {{ $batasWaktu->diffForHumans() }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Belum ada deadline</span>
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="deadline" class="form-label fw-semibold">
                                        <i class='bx bx-calendar me-1'></i>
                                        Deadline Baru <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" class="form-control form-control-lg" id="deadline"
                                        name="deadline" required
                                        value="{{ $batasWaktu ? $batasWaktu->format('Y-m-d\\TH:i') : \Carbon\Carbon::now()->addDays(3)->endOfDay()->format('Y-m-d\\TH:i') }}">
                                    <div class="form-text">
                                        <i class='bx bx-info-circle me-1'></i>
                                        Pilih tanggal dan waktu deadline baru untuk input hasil fasilitasi
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label fw-semibold">
                                        <i class='bx bx-message-detail me-1'></i>
                                        Keterangan / Alasan
                                    </label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="4"
                                        placeholder="Jelaskan alasan perubahan deadline..." required></textarea>
                                    <div class="form-text">Minimal 20 karakter</div>
                                </div>
                            </div>

                            <div class="modal-footer border-top-0 bg-light">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class='bx bx-x me-1'></i>Batal
                                </button>
                                <button type="submit" class="btn btn-warning shadow-sm">
                                    <i class='bx bx-check me-1'></i>Update Deadline
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
