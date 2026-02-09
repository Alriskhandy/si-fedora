@extends('layouts.app')

@section('title', 'Detail Penetapan Jadwal')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Penetapan Jadwal Fasilitasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penetapan-jadwal.index') }}">Penetapan Jadwal</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
        </div>

        <div class="row">
            <!-- Informasi Permohonan -->
            <div class="col-lg-4">
                <!-- Informasi Penetapan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Penetapan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Ditetapkan Oleh</label>
                            <p class="mb-0">
                                <i class='bx bx-user'></i> {{ $penetapan->penetap->name ?? '-' }}
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="mb-0">
                                <i class='bx bx-calendar'></i> {{ $penetapan->tanggal_penetapan->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Kabupaten/Kota</label>
                            <p class="fw-bold mb-0">{{ $permohonan->kabupatenKota->nama ?? '-' }}</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Jenis Dokumen</label>
                            <p class="mb-0">
                                <span
                                    class="badge bg-primary">{{ strtoupper($permohonan->jenisDokumen->nama ?? '-') }}</span>
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Tahun</label>
                            <p class="fw-bold mb-0">{{ $permohonan->tahun }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Jadwal -->
            <div class="col-lg-8">
                <!-- Informasi Jadwal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-calendar'></i> Jadwal Fasilitasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="text-muted small">Tanggal Mulai</label>
                                <h5 class="mb-0">
                                    <i class='bx bx-calendar-event text-primary'></i>
                                    {{ $penetapan->tanggal_mulai->format('d M Y') }}
                                </h5>
                                <small class="text-muted">{{ $penetapan->tanggal_mulai->format('l') }}</small>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Tanggal Selesai</label>
                                <h5 class="mb-0">
                                    <i class='bx bx-calendar-check text-success'></i>
                                    {{ $penetapan->tanggal_selesai->format('d M Y') }}
                                </h5>
                                <small class="text-muted">{{ $penetapan->tanggal_selesai->format('l') }}</small>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Durasi Fasilitasi</label>
                                <h5 class="mb-0">
                                    <i class='bx bx-time text-primary'></i>
                                    {{ $penetapan->durasi_hari }} hari
                                </h5>
                                <small class="text-muted">{{ $penetapan->durasi_hari }} hari</small>
                            </div>
                        </div>

                        @if ($penetapan->lokasi)
                            <div class="mb-3">
                                <label class="text-muted small">Lokasi Pelaksanaan</label>
                                <p class="mb-0">
                                    <i class='bx bx-map text-danger'></i>
                                    {{ $penetapan->lokasi }}
                                </p>
                            </div>
                        @endif

                        @if ($penetapan->latitude && $penetapan->longitude)
                            <hr>
                            <div class="mb-3">
                                <label class="text-muted small mb-2">
                                    <i class='bx bx-map-pin text-danger'></i> Lokasi di Peta
                                </label>
                                <div id="map"
                                    style="height: 350px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class='bx bx-current-location'></i>
                                        Koordinat: {{ number_format($penetapan->latitude, 6) }},
                                        {{ number_format($penetapan->longitude, 6) }}
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @if ($penetapan->latitude && $penetapan->longitude)
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endif
@endpush

@push('scripts')
    @if ($penetapan->latitude && $penetapan->longitude)
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            $(document).ready(function() {
                const lat = {{ $penetapan->latitude }};
                const lng = {{ $penetapan->longitude }};

                // Create map centered on the location
                const map = L.map('map').setView([lat, lng], 15);

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
                const marker = L.marker([lat, lng], {
                    icon: customIcon
                }).addTo(map);

                // Add popup
                let popupContent = '<b>Lokasi Pelaksanaan</b><br>';
                @if ($penetapan->lokasi)
                    popupContent += '{{ addslashes($penetapan->lokasi) }}<br>';
                @endif
                popupContent += `<small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small>`;

                marker.bindPopup(popupContent).openPopup();
            });
        </script>
    @endif
@endpush
