@extends('layouts.app')

@section('title', 'Penetapan Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Penetapan Jadwal Fasilitasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penetapan-jadwal.index') }}">Penetapan Jadwal</a></li>
                        <li class="breadcrumb-item active">Buat Penetapan</li>
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
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Status Laporan</label>
                            <p class="mb-0">
                                @if ($permohonan->laporanVerifikasi)
                                    <span class="badge bg-success">
                                        <i class='bx bx-check'></i>
                                        {{ ucfirst($permohonan->laporanVerifikasi->status_kelengkapan) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Belum Ada Laporan</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Jadwal Tersedia -->
                @if ($jadwalTersedia->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class='bx bx-calendar-event'></i> Jadwal Tersedia
                            </h5>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">Ada {{ $jadwalTersedia->count() }} jadwal fasilitasi yang
                                tersedia</small>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Form Penetapan Jadwal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Form Penetapan Jadwal</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('penetapan-jadwal.store', $permohonan) }}" method="POST">
                            @csrf

                            <!-- Pilih Jadwal Fasilitasi (Optional) -->
                            <div class="mb-4">
                                <label for="jadwal_fasilitasi_id" class="form-label">
                                    Jadwal Fasilitasi yang Sudah Ada (Opsional)
                                </label>
                                <select name="jadwal_fasilitasi_id" id="jadwal_fasilitasi_id"
                                    class="form-select @error('jadwal_fasilitasi_id') is-invalid @enderror">
                                    <option value="">-- Buat Jadwal Baru atau Pilih dari yang Ada --</option>
                                    @foreach ($jadwalTersedia as $jadwal)
                                        <option value="{{ $jadwal->id }}"
                                            data-mulai="{{ $jadwal->tanggal_mulai->format('Y-m-d') }}"
                                            data-selesai="{{ $jadwal->tanggal_selesai->format('Y-m-d') }}"
                                            {{ old('jadwal_fasilitasi_id') == $jadwal->id ? 'selected' : '' }}>
                                            {{ $jadwal->jenisDokumen->nama ?? '-' }}
                                            @if ($jadwal->keterangan)
                                                ({{ $jadwal->keterangan }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Pilih jadwal yang sudah ada atau buat jadwal baru dengan mengisi tanggal di bawah
                                </small>
                                @error('jadwal_fasilitasi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class='bx bx-info-circle'></i>
                                <strong>Info:</strong> Jika memilih jadwal yang sudah ada, tanggal akan otomatis terisi.
                                Anda masih bisa mengubahnya jika diperlukan.
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="mb-4">
                                <label for="tanggal_mulai" class="form-label">
                                    Tanggal Mulai Fasilitasi <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                    class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                    value="{{ old('tanggal_mulai') }}" min="{{ date('Y-m-d') }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="mb-4">
                                <label for="tanggal_selesai" class="form-label">
                                    Tanggal Selesai Fasilitasi <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                    class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                    value="{{ old('tanggal_selesai') }}" min="{{ date('Y-m-d') }}" required>
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-4">
                                <label for="lokasi" class="form-label">
                                    Lokasi Pelaksanaan (Opsional)
                                </label>
                                <input type="text" name="lokasi" id="lokasi"
                                    class="form-control @error('lokasi') is-invalid @enderror" value="{{ old('lokasi') }}"
                                    placeholder="Contoh: Ruang Rapat Badan, Hotel XYZ, dll">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tagging Lokasi (Geolocation) -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class='bx bx-map'></i> Tagging Lokasi di Peta (Opsional)
                                </label>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3">
                                            <i class='bx bx-info-circle'></i>
                                            <small>Klik pada peta untuk menandai lokasi pelaksanaan. Koordinat akan otomatis
                                                tersimpan.</small>
                                        </div>

                                        <!-- Map Container -->
                                        <div id="map"
                                            style="height: 350px; width: 100%; border-radius: 8px; border: 1px solid #ddd;">
                                        </div>

                                        <!-- Koordinat Display -->
                                        <div class="mt-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Latitude</label>
                                                    <input type="text" name="latitude" id="latitude"
                                                        class="form-control form-control-sm @error('latitude') is-invalid @enderror"
                                                        value="{{ old('latitude') }}" readonly placeholder="-6.200000">
                                                    @error('latitude')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Longitude</label>
                                                    <input type="text" name="longitude" id="longitude"
                                                        class="form-control form-control-sm @error('longitude') is-invalid @enderror"
                                                        value="{{ old('longitude') }}" readonly placeholder="106.816666">
                                                    @error('longitude')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    id="clearLocation">
                                                    <i class='bx bx-trash'></i> Hapus Lokasi
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    id="getCurrentLocation">
                                                    <i class='bx bx-current-location'></i> Gunakan Lokasi Saat Ini
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-secondary">
                                    <i class='bx bx-x'></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class='bx bx-calendar-check'></i> Tetapkan Jadwal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Leaflet Control Geocoder CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        .leaflet-container {
            cursor: crosshair;
        }

        .leaflet-control-geocoder {
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4);
        }

        .leaflet-control-geocoder-form input {
            font-size: 14px;
            padding: 5px 10px;
            width: 250px;
        }
    </style>
@endpush

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Leaflet Control Geocoder JS -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize map centered on Maluku Utara (Sofifi/Ternate)
            const defaultLat = 0.7893;
            const defaultLng = 127.3879;
            let marker = null;

            // Create map
            const map = L.map('map').setView([defaultLat, defaultLng], 11);

            // Add Google Maps tiles (Hybrid: Satellite + Roads)
            L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; <a href="https://maps.google.com">Google Maps</a>'
            }).addTo(map);

            // Add Search Control (Geocoder)
            const geocoder = L.Control.geocoder({
                    defaultMarkGeocode: false,
                    placeholder: 'Cari lokasi...',
                    errorMessage: 'Lokasi tidak ditemukan',
                    geocoder: L.Control.Geocoder.nominatim({
                        geocodingQueryParams: {
                            countrycodes: 'id', // Limit ke Indonesia
                            'accept-language': 'id'
                        }
                    })
                })
                .on('markgeocode', function(e) {
                    const latlng = e.geocode.center;
                    setMarker(latlng.lat, latlng.lng);
                    map.setView(latlng, 16);
                })
                .addTo(map);

            // Custom icon for marker
            const customIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Function to add/update marker
            function setMarker(lat, lng) {
                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }

                // Add new marker
                marker = L.marker([lat, lng], {
                    icon: customIcon
                }).addTo(map);
                marker.bindPopup(`<b>Lokasi Terpilih</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`)
                    .openPopup();

                // Update form fields
                $('#latitude').val(lat.toFixed(8));
                $('#longitude').val(lng.toFixed(8));

                // Pan to marker
                map.panTo([lat, lng]);
            }

            // Click on map to set location
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                setMarker(lat, lng);
            });

            // Clear location button
            $('#clearLocation').on('click', function() {
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                $('#latitude').val('');
                $('#longitude').val('');
            });

            // Get current location button
            $('#getCurrentLocation').on('click', function() {
                const btn = $(this);
                const originalText = btn.html();

                btn.prop('disabled', true).html(
                    '<i class="bx bx-loader bx-spin"></i> Mendapatkan lokasi...');

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            setMarker(lat, lng);
                            map.setView([lat, lng], 16);

                            btn.prop('disabled', false).html(originalText);
                        },
                        function(error) {
                            alert('Gagal mendapatkan lokasi: ' + error.message);
                            btn.prop('disabled', false).html(originalText);
                        }, {
                            enableHighAccuracy: true,
                            timeout: 5000,
                            maximumAge: 0
                        }
                    );
                } else {
                    alert('Browser Anda tidak mendukung Geolocation');
                    btn.prop('disabled', false).html(originalText);
                }
            });

            // Load existing coordinates if any (for edit mode)
            const existingLat = $('#latitude').val();
            const existingLng = $('#longitude').val();
            if (existingLat && existingLng) {
                setMarker(parseFloat(existingLat), parseFloat(existingLng));
                map.setView([parseFloat(existingLat), parseFloat(existingLng)], 16);
            }

            // Auto-fill tanggal ketika memilih jadwal yang sudah ada
            $('#jadwal_fasilitasi_id').on('change', function() {
                const selected = $(this).find(':selected');
                const tanggalMulai = selected.data('mulai');
                const tanggalSelesai = selected.data('selesai');

                if (tanggalMulai && tanggalSelesai) {
                    $('#tanggal_mulai').val(tanggalMulai);
                    $('#tanggal_selesai').val(tanggalSelesai);
                }
            });

            // Validasi tanggal selesai harus >= tanggal mulai
            $('#tanggal_mulai').on('change', function() {
                const tanggalMulai = $(this).val();
                $('#tanggal_selesai').attr('min', tanggalMulai);

                // Reset tanggal selesai jika lebih kecil dari mulai
                const tanggalSelesai = $('#tanggal_selesai').val();
                if (tanggalSelesai && tanggalSelesai < tanggalMulai) {
                    $('#tanggal_selesai').val(tanggalMulai);
                }
            });
        });
    </script>
@endpush
