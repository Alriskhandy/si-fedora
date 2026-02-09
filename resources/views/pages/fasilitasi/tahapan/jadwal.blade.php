@extends('layouts.app')

@section('title', 'Tahapan Penetapan Jadwal')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
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
    @php
        $isKaban = auth()->user()->hasRole('kaban');
        $isAdmin = auth()->user()->hasRole('admin_peran');
        $isPemohon = auth()->user()->hasRole('pemohon');
        $isTimFedora = auth()
            ->user()
            ->hasAnyRole(['verifikator', 'fasilitator']);
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

        @if (!$permohonan->penetapanJadwal)
            <!-- Jadwal Belum Ditetapkan -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class='bx bx-calendar-x text-primary' style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="mb-2 text-dark">Jadwal Belum Ditetapkan</h5>
                    <p class="text-muted mb-4">Belum ada jadwal pelaksanaan fasilitasi / evaluasi, silahkan kembali lagi
                        setelah jadwal ditetapkan.
                    </p>

                    @if ($isKaban)
                        <a href="{{ route('penetapan-jadwal.create', $permohonan) }}" class="btn btn-primary">
                            <i class='bx bx-calendar-plus me-1'></i>Tetapkan Jadwal
                        </a>
                    @endif
                </div>
            </div>
        @else
            <!-- Jadwal Sudah Ditetapkan -->
            @if ($isKaban || $isAdmin)
                <!-- Tampilan untuk Kaban dan Admin - Informasi Lengkap Penetapan Jadwal -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class='bx bx-calendar-check me-2'></i>Penetapan Jadwal Pelaksanaan Fasilitasi
                        </h5>
                        <div>
                            @if ($isKaban)
                                <a href="{{ route('penetapan-jadwal.create', $permohonan) }}" class="btn btn-primary btn-sm">
                                    <i class='bx bx-edit-alt me-1'></i>Ubah Jadwal
                                </a>
                            @endif
                            @if ($isAdmin && !$permohonan->undanganPelaksanaan)
                                <a href="{{ route('undangan-pelaksanaan.create', $permohonan) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class='bx bx-envelope me-1'></i>Buat Undangan
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">
                                    <i class='bx bx-info-circle me-2'></i>Informasi Jadwal Pelaksanaan
                                </h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-user-check me-1'></i> Ditetapkan Oleh:
                                        </td>
                                        <td><strong>{{ $permohonan->penetapanJadwal->penetap->name ?? '-' }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-building me-1'></i>
                                            Kabupaten/Kota:</td>
                                        <td><strong>{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-file me-1'></i> Jenis Dokumen:</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="text-muted"><i class='bx bx-calendar-event me-1'></i>
                                            Tanggal Mulai:</td>
                                        <td>
                                            <strong>{{ $permohonan->penetapanJadwal->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d F Y') : '-' }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-calendar-event me-1'></i>
                                            Tanggal Selesai:</td>
                                        <td>
                                            <strong>{{ $permohonan->penetapanJadwal->tanggal_selesai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d F Y') : '-' }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-map me-1'></i> Lokasi:</td>
                                        <td><strong>{{ $permohonan->penetapanJadwal->lokasi ?? '-' }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">
                                    <i class='bx bx-map-alt me-2'></i>Lokasi Pelaksanaan
                                </h6>
                                @if ($permohonan->penetapanJadwal->latitude && $permohonan->penetapanJadwal->longitude)
                                    <div id="map" style="height: 300px; border-radius: 8px;"></div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class='bx bx-map-pin me-1'></i>
                                        {{ $permohonan->penetapanJadwal->latitude }},
                                        {{ $permohonan->penetapanJadwal->longitude }}
                                    </small>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class='bx bx-info-circle me-2'></i>
                                        Koordinat lokasi tidak tersedia
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Undangan -->
                        @if ($permohonan->undanganPelaksanaan)
                            <div class="alert alert-success border-0 mt-3">
                                <i class='bx bx-check-circle me-2'></i>
                                <strong>Undangan Telah Dibuat</strong><br>
                                <small>Nomor: {{ $permohonan->undanganPelaksanaan->nomor_surat ?? '-' }} |
                                    Tanggal:
                                    {{ $permohonan->undanganPelaksanaan->tanggal_surat ? \Carbon\Carbon::parse($permohonan->undanganPelaksanaan->tanggal_surat)->format('d M Y') : '-' }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($permohonan->undanganPelaksanaan)
                <!-- Tampilan untuk Role Lain (Pemohon, Tim Fedora) - Hanya Jika Undangan Sudah Ada -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class='bx bx-calendar-check me-2'></i>Jadwal Pelaksanaan Fasilitasi
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">
                                    <i class='bx bx-info-circle me-2'></i>Informasi Jadwal Pelaksanaan
                                </h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td width="40%" class="text-muted"><i class='bx bx-calendar-event me-1'></i>
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
                                                @if ($permohonan->undanganPelaksanaan->waktu_mulai)
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
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-building me-1'></i> Kabupaten/Kota:</td>
                                        <td><strong>{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class='bx bx-file me-1'></i> Jenis Dokumen:</td>
                                        <td>
                                            <span class="badge bg-primary">
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
                                            @if ($permohonan->undanganPelaksanaan->file_undangan)
                                                <a href="{{ asset('storage/' . $permohonan->undanganPelaksanaan->file_undangan) }}"
                                                    target="_blank" class="btn btn-sm btn-primary">
                                                    <i class='bx bx-download'></i> Download
                                                </a>
                                            @else
                                                <span class="text-muted">Belum tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">
                                    <i class='bx bx-map-alt me-2'></i>Lokasi Pelaksanaan
                                </h6>
                                @if ($permohonan->penetapanJadwal->latitude && $permohonan->penetapanJadwal->longitude)
                                    <div id="map" style="height: 300px; border-radius: 8px;"></div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class='bx bx-map-pin me-1'></i>
                                        {{ $permohonan->penetapanJadwal->latitude }},
                                        {{ $permohonan->penetapanJadwal->longitude }}
                                    </small>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class='bx bx-info-circle me-2'></i>
                                        Koordinat lokasi tidak tersedia
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Tampilan untuk Role Lain Jika Undangan Belum Ada -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class='bx bx-envelope-open text-primary' style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="mb-2 text-dark">Jadwal Belum Ditetapkan</h5>
                        <p class="text-muted mb-4">Belum ada jadwal pelaksanaan fasilitasi / evaluasi, silahkan kembali
                            lagi
                            setelah jadwal ditetapkan.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Undangan Pelaksanaan -->
            @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->file_undangan)
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class='bx bx-envelope me-2'></i>Undangan Pelaksanaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light"
                                            style="width: 50px; height: 50px;">
                                            <i class='bx bx-file-blank text-primary' style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">
                                            <strong>Nomor Surat:</strong>
                                            <span
                                                class="text-muted">{{ $permohonan->undanganPelaksanaan->nomor_surat ?? '-' }}</span>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Tanggal Surat:</strong>
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

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        @if ($permohonan->penetapanJadwal && $permohonan->penetapanJadwal->latitude && $permohonan->penetapanJadwal->longitude)
            // Initialize map
            const latitude = {{ $permohonan->penetapanJadwal->latitude }};
            const longitude = {{ $permohonan->penetapanJadwal->longitude }};
            const lokasi = "{{ $permohonan->penetapanJadwal->lokasi ?? 'Lokasi Pelaksanaan' }}";

            const map = L.map('map').setView([latitude, longitude], 15);

            // Add Google Maps tiles (Hybrid: Satellite + Roads)
            L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; <a href="https://maps.google.com">Google Maps</a>'
            }).addTo(map);

            // Custom icon for marker
            const customIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Add marker
            const marker = L.marker([latitude, longitude]).addTo(map);
            marker.bindPopup(`<b>${lokasi}</b><br>Lat: ${latitude}<br>Lng: ${longitude}`).openPopup();
        @endif
    </script>
@endpush
