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
            $masterTahapanHasil = \App\Models\MasterTahapan::where('nama_tahapan', 'Hasil Fasilitasi / Evaluasi')->first();
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
        @if ($tahapanHasil && $batasWaktu && (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']) || $isFasilitator))
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
                                @if ($isFasilitator || auth()->user()->hasAnyRole(['admin_peran', 'superadmin', 'kaban']))
                                    @if (!$permohonan->hasilFasilitasi || $permohonan->hasilFasilitasi->status_validasi !== 'tervalidasi')
                                        <a href="{{ route('hasil-fasilitasi.create', $permohonan) }}" class="btn btn-primary shadow-sm">
                                            <i class='bx {{ !$permohonan->hasilFasilitasi ? "bx-plus-circle" : "bx-edit" }} me-1'></i>
                                            {{ !$permohonan->hasilFasilitasi ? 'Input' : 'Lihat/Edit' }} Hasil
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

        @if (!$permohonan->hasilFasilitasi)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 mb-3"
                            style="width: 80px; height: 80px;">
                            <i class='bx bx-time-five bx-lg text-warning'></i>
                        </div>
                    </div>
                    <h5 class="mb-3">Hasil Fasilitasi Sedang Diproses</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                        Hasil fasilitasi sedang dalam proses input oleh fasilitator.
                        Dokumen akan tersedia setelah diinput dan divalidasi oleh Kepala Badan
                    </p>

                    @if (!$batasWaktu && ($isFasilitator || auth()->user()->hasAnyRole(['admin_peran', 'superadmin', 'kaban'])))
                        <div>
                            <a href="{{ route('hasil-fasilitasi.create', $permohonan) }}" class="btn btn-primary btn-lg">
                                <i class='bx bx-plus-circle me-2'></i>Input Hasil Fasilitasi
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($permohonan->hasilFasilitasi->status_validasi !== 'tervalidasi')
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 mb-3"
                            style="width: 80px; height: 80px;">
                            <i class='bx bx-time-five bx-lg text-dark'></i>
                        </div>
                    </div>
                    <h5 class="mb-2">Sedang Diproses</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                        Catatan / masukan sedang dalam proses penginputan. <br>
                        Silahkan kembali lagi nanti.
                    </p>
                </div>
            </div>
        @else
            <!-- Hasil sudah tervalidasi - semua role bisa lihat -->
            <!-- Informasi Hasil Fasilitasi -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-white"
                                style="width: 45px; height: 45px;">
                                <i class='bx bx-check-double text-success' style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 text-white">
                                <i class='bx bx-check-circle me-2'></i>Hasil Fasilitasi & Catatan Penyempurnaan
                            </h5>
                            <p class="mb-0 text-white-50 small">Telah divalidasi dan siap digunakan</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded h-100">
                                <h6 class="mb-3 text-uppercase text-muted small fw-bold">
                                    <i class='bx bx-info-circle me-1'></i>Informasi Umum
                                </h6>
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Tanggal Pelaksanaan</label>
                                    <div class="fw-bold">
                                        <i class='bx bx-calendar text-primary me-1'></i>
                                        {{ $permohonan->hasilFasilitasi->tanggal_pelaksanaan ? \Carbon\Carbon::parse($permohonan->hasilFasilitasi->tanggal_pelaksanaan)->format('d F Y') : '-' }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Status Validasi</label>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class='bx bx-check-shield me-1'></i>Tervalidasi
                                    </span>
                                </div>
                                <div>
                                    <label class="text-muted small d-block mb-1">Diinput Oleh</label>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
                                                style="width: 35px; height: 35px;">
                                                <i class='bx bx-user text-primary'></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $permohonan->hasilFasilitasi->pembuat->name ?? '-' }}
                                            </div>
                                            <small class="text-muted">Fasilitator</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if ($permohonan->hasilFasilitasi->catatan_kaban)
                                <div
                                    class="p-3 bg-primary bg-opacity-5 rounded border border-primary border-opacity-25 h-100">
                                    <h6 class="mb-3 text-uppercase text-primary small fw-bold">
                                        <i class='bx bx-message-dots me-1'></i>Catatan Kepala Badan
                                    </h6>
                                    <p class="mb-0 text-dark">{{ $permohonan->hasilFasilitasi->catatan_kaban }}</p>
                                </div>
                            @else
                                <div
                                    class="p-3 bg-light rounded border border-dashed h-100 d-flex align-items-center justify-content-center">
                                    <div class="text-center text-muted">
                                        <i class='bx bx-message-x bx-lg mb-2 d-block'></i>
                                        <small>Tidak ada catatan dari Kepala Badan</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hasil Fasilitasi per Sistematika -->
            @if ($permohonan->hasilFasilitasi->hasilFasilitasiSistematika->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <i class='bx bx-list-ul text-primary me-2'></i>Hasil Fasilitasi per Sistematika
                            </h5>
                            <span class="badge bg-primary rounded-pill">
                                {{ $permohonan->hasilFasilitasi->hasilFasilitasiSistematika->count() }} Sistematika
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accordion accordion-flush" id="accordionSistematika">
                            @foreach ($permohonan->hasilFasilitasi->hasilFasilitasiSistematika as $index => $sistematika)
                                <div class="accordion-item border rounded mb-2">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} shadow-none"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $index }}"
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse{{ $index }}">
                                            <div class="d-flex align-items-center w-100">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
                                                        style="width: 35px; height: 35px;">
                                                        <i class='bx bx-book-content text-primary'></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <strong>{{ $sistematika->masterBab->nama_bab ?? 'Sistematika' }}</strong>
                                                    @if ($sistematika->saran)
                                                        <span class="badge bg-info ms-2">
                                                            <i class='bx bx-bulb'></i> Ada Saran
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}"
                                        class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                        aria-labelledby="heading{{ $index }}"
                                        data-bs-parent="#accordionSistematika">
                                        <div class="accordion-body bg-light">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-muted small fw-bold mb-2">
                                                    <i class='bx bx-detail me-1'></i>Keterangan
                                                </h6>
                                                <p class="mb-0">{{ $sistematika->keterangan ?? '-' }}</p>
                                            </div>
                                            @if ($sistematika->saran)
                                                <div
                                                    class="p-3 bg-info bg-opacity-10 rounded border border-info border-opacity-25">
                                                    <h6 class="text-info mb-2">
                                                        <i class='bx bx-bulb me-1'></i>Saran Penyempurnaan
                                                    </h6>
                                                    <p class="mb-0">{{ $sistematika->saran }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Hasil Fasilitasi per Urusan -->
            @if ($permohonan->hasilFasilitasi->hasilFasilitasiUrusan->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <i class='bx bx-briefcase text-primary me-2'></i>Hasil Fasilitasi per Urusan
                            </h5>
                            <span class="badge bg-primary rounded-pill">
                                {{ $permohonan->hasilFasilitasi->hasilFasilitasiUrusan->count() }} Urusan
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="30%">
                                            <i class='bx bx-briefcase me-1'></i>Urusan
                                        </th>
                                        <th width="35%">
                                            <i class='bx bx-detail me-1'></i>Keterangan
                                        </th>
                                        <th width="30%">
                                            <i class='bx bx-bulb me-1'></i>Saran
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permohonan->hasilFasilitasi->hasilFasilitasiUrusan as $index => $urusan)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark rounded-circle"
                                                    style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10"
                                                            style="width: 32px; height: 32px;">
                                                            <i class='bx bx-food-menu text-primary'></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold">
                                                            {{ $urusan->masterUrusan->nama_urusan ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $urusan->keterangan ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @if ($urusan->saran)
                                                    <div class="p-2 bg-info bg-opacity-10 rounded">
                                                        <i class='bx bx-bulb text-info'></i>
                                                        <span class="text-info small">{{ $urusan->saran }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class='bx bx-minus'></i> Tidak ada saran
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Download Laporan -->
            @if ($permohonan->hasilFasilitasi->status_validasi === 'tervalidasi')
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-3"
                                style="width: 80px; height: 80px;">
                                <i class='bx bx-file-blank bx-lg text-primary'></i>
                            </div>
                        </div>
                        <h4 class="mb-3">Dokumen Hasil Fasilitasi</h4>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                            Download laporan hasil fasilitasi yang telah tervalidasi dalam format PDF
                        </p>
                        <a href="{{ route('hasil-fasilitasi.download', $permohonan) }}"
                            class="btn btn-primary btn-lg shadow-sm" target="_blank">
                            <i class='bx bx-download me-2'></i>Download Laporan PDF
                        </a>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class='bx bx-check-shield me-1'></i>
                                Dokumen telah tervalidasi dan siap digunakan
                            </small>
                        </div>
                    </div>
                </div>
            @endif
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
                                                @if($batasWaktu)
                                                    {{ $batasWaktu->format('d F Y, H:i') }}
                                                    <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning text-dark' }} ms-2">
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
                                    <input type="datetime-local" class="form-control form-control-lg"
                                        id="deadline" name="deadline" required
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
