@extends('layouts.app')

@section('title', 'Tahapan Penetapan Jadwal')

@section('main')
    @php
        $isKaban = auth()->user()->hasRole('kaban');
        $isAdmin = auth()
            ->user()
            ->hasAnyRole(['admin_peran', 'kaban', 'superadmin']);
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Jadwal Pelaksanaan Fasilitasi/Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail</a></li>
                        <li class="breadcrumb-item active">Tahapan Jadwal</li>
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

        {{-- Debug Info - hanya untuk admin --}}
        @if (config('app.debug') && $isAdmin)
            <div class="alert alert-info mb-3">
                <strong>Debug Info:</strong><br>
                - PenetapanJadwal: {{ $permohonan->penetapanJadwal ? 'Ada' : 'Tidak Ada' }}<br>
                - UndanganPelaksanaan: {{ $permohonan->undanganPelaksanaan ? 'Ada' : 'Tidak Ada' }}<br>
                - Koordinator: {{ $permohonan->koordinator ? 'Ada' : 'Tidak Ada' }}<br>
                @if ($permohonan->koordinator)
                    - Koordinator->koordinator: {{ $permohonan->koordinator->koordinator ? 'Ada' : 'Tidak Ada' }}<br>
                @endif
                @if ($permohonan->undanganPelaksanaan)
                    - Waktu Mulai: {{ $permohonan->undanganPelaksanaan->waktu_mulai ?? 'Null' }}<br>
                    - File Undangan: {{ $permohonan->undanganPelaksanaan->file_undangan ?? 'Null' }}<br>
                @endif
            </div>
        @endif

        @if (!$permohonan->penetapanJadwal)
            <!-- Jadwal Belum Ditetapkan -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class='bx bx-calendar-x' style="font-size: 4rem; color: #a0d9ef;"></i>
                    </div>
                    <h5 class="mb-2" style="color: #0d3b4d;">Jadwal Belum Ditetapkan</h5>
                    <p class="text-muted mb-4">Belum ada jadwal pelaksanaan fasilitasi yang ditetapkan untuk permohonan ini.
                    </p>

                    @if ($isKaban)
                        <a href="{{ route('penetapan-jadwal.create', $permohonan) }}" class="btn btn-primary">
                            <i class='bx bx-calendar-plus me-1'></i>Tetapkan Jadwal
                        </a>
                    @else
                        <p class="text-muted small">Menunggu penetapan jadwal oleh Kepala Badan</p>
                    @endif
                </div>
            </div>
        @else
            <!-- Jadwal Sudah Ditetapkan -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background: linear-gradient(135deg, #a0d9ef 0%, #7bc4e3 100%); border: none;">
                    <h5 class="mb-0 text-white">
                        <i class='bx bx-calendar-check me-2'></i>Penetapan Jadwal Pelaksanaan Fasilitasi
                    </h5>
                    @if ($isKaban)
                        <a href="{{ route('penetapan-jadwal.create', $permohonan) }}" class="btn btn-light btn-sm">
                            <i class='bx bx-edit-alt me-1'></i>Ubah Jadwal
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">

                    <div class="row">
                        <div class="col-12">
                            <div class="info-card p-4"
                                style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #a0d9ef;">
                                <h6 class="mb-4" style="color: #0d3b4d;">
                                    <i class='bx bx-calendar me-2'></i>Informasi Jadwal Pelaksanaan
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td width="40%" class="text-muted"><i
                                                        class='bx bx-calendar-event me-1'></i>
                                                    Tanggal:</td>
                                                <td>
                                                    <strong>{{ $permohonan->penetapanJadwal->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d F Y') : '-' }}</strong>
                                                    @if (
                                                        $permohonan->penetapanJadwal->tanggal_selesai &&
                                                            $permohonan->penetapanJadwal->tanggal_mulai != $permohonan->penetapanJadwal->tanggal_selesai)
                                                        <br><small class="text-muted">s/d
                                                            {{ \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d F Y') }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted"><i class='bx bx-time me-1'></i> Waktu:</td>
                                                <td>
                                                    <strong>
                                                        @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->waktu_mulai)
                                                            {{ $permohonan->undanganPelaksanaan->waktu_mulai }}
                                                            @if ($permohonan->undanganPelaksanaan->waktu_selesai)
                                                                - {{ $permohonan->undanganPelaksanaan->waktu_selesai }}
                                                            @endif
                                                            WIB
                                                        @else
                                                            -
                                                        @endif
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted"><i class='bx bx-map me-1'></i> Tempat:</td>
                                                <td><strong>{{ $permohonan->penetapanJadwal->lokasi ?? '-' }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td width="40%" class="text-muted"><i class='bx bx-file me-1'></i> Jenis
                                                    Dokumen:</td>
                                                <td>
                                                    <span class="badge" style="background-color: #a0d9ef; color: #0d3b4d;">
                                                        {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted"><i class='bx bx-user me-1'></i> Koordinator:</td>
                                                <td><strong>{{ $permohonan->koordinator->koordinator->name ?? '-' }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted"><i class='bx bx-envelope me-1'></i> Undangan:</td>
                                                <td>
                                                    @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->file_undangan)
                                                        <a href="{{ asset('storage/' . $permohonan->undanganPelaksanaan->file_undangan) }}"
                                                            target="_blank" class="btn btn-sm btn-primary">
                                                            <i class='bx bx-download'></i> Download Undangan
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Belum tersedia</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($permohonan->penetapanJadwal->catatan)
                        <div class="alert border-0 mt-4"
                            style="background-color: rgba(160, 217, 239, 0.15); border-left: 4px solid #a0d9ef !important;">
                            <strong style="color: #0d3b4d;"><i class='bx bx-note me-1'></i>Catatan:</strong><br>
                            <span style="color: #0d3b4d;">{{ $permohonan->penetapanJadwal->catatan }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Undangan Pelaksanaan -->
            @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->file_undangan)
                <div class="card border-0 shadow-sm">
                    <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #a0d9ef;">
                        <h5 class="mb-0" style="color: #0d3b4d;">
                            <i class='bx bx-envelope me-2'></i>Undangan Pelaksanaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px; background-color: rgba(160, 217, 239, 0.2);">
                                            <i class='bx bx-file-blank' style="font-size: 1.5rem; color: #0d3b4d;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">
                                            <strong style="color: #0d3b4d;">Nomor Surat:</strong>
                                            <span
                                                class="text-muted">{{ $permohonan->undanganPelaksanaan->nomor_surat ?? '-' }}</span>
                                        </p>
                                        <p class="mb-0">
                                            <strong style="color: #0d3b4d;">Tanggal Surat:</strong>
                                            <span
                                                class="text-muted">{{ $permohonan->undanganPelaksanaan->tanggal_surat ? \Carbon\Carbon::parse($permohonan->undanganPelaksanaan->tanggal_surat)->format('d F Y') : '-' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ asset('storage/' . $permohonan->undanganPelaksanaan->file_undangan) }}"
                                    target="_blank" class="btn btn-primary">
                                    <i class='bx bx-download'></i> Download Undangan
                                </a>
                            </div>
                        </div>

                        <!-- Konfirmasi Kehadiran -->
                        @if (auth()->user()->hasRole('pemohon'))
                            @if ($permohonan->undanganPelaksanaan->konfirmasi_kehadiran === null)
                                <div class="alert alert-warning border-0 mb-3"
                                    style="background-color: rgba(255, 193, 7, 0.1);">
                                    <i class='bx bx-bell me-2'></i>
                                    <strong>Perhatian:</strong> Mohon konfirmasi kehadiran Anda untuk pelaksanaan
                                    fasilitasi.
                                </div>
                                <div class="d-flex gap-2">
                                    <form
                                        action="{{ route('undangan-pelaksanaan.konfirmasi', $permohonan->undanganPelaksanaan) }}"
                                        method="POST" class="flex-fill">
                                        @csrf
                                        <input type="hidden" name="konfirmasi_kehadiran" value="1">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class='bx bx-check-circle'></i> Konfirmasi Hadir
                                        </button>
                                    </form>
                                    <form
                                        action="{{ route('undangan-pelaksanaan.konfirmasi', $permohonan->undanganPelaksanaan) }}"
                                        method="POST" class="flex-fill">
                                        @csrf
                                        <input type="hidden" name="konfirmasi_kehadiran" value="0">
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class='bx bx-x-circle'></i> Tidak Hadir
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="alert border-0 mb-0
                            {{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'alert-success' : 'alert-danger' }}"
                                    style="background-color: rgba({{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? '40, 167, 69' : '220, 53, 69' }}, 0.1);">
                                    <i
                                        class='bx bx-{{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'check-circle' : 'x-circle' }} me-2'></i>
                                    <strong>Status Kehadiran:</strong>
                                    {{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'Hadir' : 'Tidak Hadir' }}
                                    @if ($permohonan->undanganPelaksanaan->keterangan_kehadiran)
                                        <br><small>{{ $permohonan->undanganPelaksanaan->keterangan_kehadiran }}</small>
                                    @endif
                                </div>
                            @endif
                        @elseif ($permohonan->undanganPelaksanaan->konfirmasi_kehadiran !== null)
                            <div class="alert border-0 mb-0
                        {{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'alert-success' : 'alert-danger' }}"
                                style="background-color: rgba({{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? '40, 167, 69' : '220, 53, 69' }}, 0.1);">
                                <i
                                    class='bx bx-{{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'check-circle' : 'x-circle' }} me-2'></i>
                                <strong>Status Kehadiran Pemohon:</strong>
                                {{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'Hadir' : 'Tidak Hadir' }}
                                @if ($permohonan->undanganPelaksanaan->keterangan_kehadiran)
                                    <br><small>{{ $permohonan->undanganPelaksanaan->keterangan_kehadiran }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
