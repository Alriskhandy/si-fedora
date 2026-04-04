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
            <div>
                @if (auth()->user()->hasRole('kaban'))
                    <button type="button" class="btn btn-primary me-2" id="btnEdit">
                        <i class='bx bx-edit'></i> Edit Jadwal
                    </button>
                    <button type="button" class="btn btn-success me-2 d-none" id="btnSave">
                        <i class='bx bx-save'></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary me-2 d-none" id="btnCancel">
                        <i class='bx bx-x'></i> Batal
                    </button>
                @endif
                <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-secondary">
                    <i class='bx bx-arrow-back'></i> Kembali
                </a>
            </div>
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

        <!-- Form Update (Hidden) -->
        <form action="{{ route('penetapan-jadwal.update', $permohonan) }}" method="POST" id="formUpdate">
            @csrf
            @method('PUT')

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
                            <!-- View Mode -->
                            <div id="viewMode">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="text-muted small">Tanggal Mulai</label>
                                        <h5 class="mb-0">
                                            <i class='bx bx-calendar-event text-primary'></i>
                                            <span
                                                id="displayTanggalMulai">{{ $penetapan->tanggal_mulai->format('d M Y') }}</span>
                                        </h5>
                                        <small class="text-muted">{{ $penetapan->tanggal_mulai->format('l') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted small">Tanggal Selesai</label>
                                        <h5 class="mb-0">
                                            <i class='bx bx-calendar-check text-success'></i>
                                            <span
                                                id="displayTanggalSelesai">{{ $penetapan->tanggal_selesai->format('d M Y') }}</span>
                                        </h5>
                                        <small class="text-muted">{{ $penetapan->tanggal_selesai->format('l') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted small">Durasi Fasilitasi</label>
                                        <h5 class="mb-0">
                                            <i class='bx bx-time text-primary'></i>
                                            <span id="displayDurasi">{{ $penetapan->durasi_hari }}</span> hari
                                        </h5>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small">Lokasi Pelaksanaan</label>
                                    <p class="mb-0">
                                        <i class='bx bx-map text-danger'></i>
                                        <span id="displayLokasi">{{ $penetapan->lokasi ?? 'Belum ditentukan' }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Edit Mode -->
                            <div id="editMode" class="d-none">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_mulai"
                                            id="inputTanggalMulai"
                                            value="{{ $penetapan->tanggal_mulai->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Selesai <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_selesai"
                                            id="inputTanggalSelesai"
                                            value="{{ $penetapan->tanggal_selesai->format('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Lokasi Pelaksanaan</label>
                                    <input type="text" class="form-control" name="lokasi" id="inputLokasi"
                                        value="{{ $penetapan->lokasi }}" placeholder="Masukkan lokasi pelaksanaan">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Latitude</label>
                                        <input type="number" step="0.000001" class="form-control" name="latitude"
                                            id="inputLatitude" value="{{ $penetapan->latitude }}"
                                            placeholder="Contoh: -0.123456">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Longitude</label>
                                        <input type="number" step="0.000001" class="form-control" name="longitude"
                                            id="inputLongitude" value="{{ $penetapan->longitude }}"
                                            placeholder="Contoh: 127.123456">
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class='bx bx-info-circle me-2'></i>
                                    <small>Klik pada peta di bawah untuk mengatur lokasi, atau masukkan koordinat secara
                                        manual.</small>
                                </div>
                            </div>

                            @if ($penetapan->latitude && $penetapan->longitude)
                                <hr>
                                <div class="mb-3">
                                    <label class="text-muted small mb-2">
                                        <i class='bx bx-map-pin text-danger'></i> Lokasi di Peta
                                    </label>
                                    <div id="map"
                                        style="height: 350px; width: 100%; border-radius: 8px; border: 1px solid #ddd;">
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class='bx bx-current-location'></i>
                                            Koordinat: <span
                                                id="displayCoordinates">{{ number_format($penetapan->latitude, 6) }},
                                                {{ number_format($penetapan->longitude, 6) }}</span>
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @if ($penetapan->latitude && $penetapan->longitude)
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endif
    <style>
        /* Fix z-index for SweetAlert2 */
        .swal2-container {
            z-index: 9999 !important;
        }
        .swal2-backdrop-show {
            z-index: 9998 !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        $(document).ready(function() {
            let map;
            let marker;
            let isEditMode = false;
            const lat = {{ $penetapan->latitude ?? 0 }};
            const lng = {{ $penetapan->longitude ?? 0 }};

            // Initialize map
            function initMap() {
                if (map) {
                    map.remove();
                }

                const initialLat = lat || -0.5;
                const initialLng = lng || 127.5;

                map = L.map('map').setView([initialLat, initialLng], lat ? 15 : 10);

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

                // Add marker if coordinates exist
                if (lat && lng) {
                    marker = L.marker([initialLat, initialLng], {
                        icon: customIcon,
                        draggable: isEditMode
                    }).addTo(map);

                    let popupContent = '<b>Lokasi Pelaksanaan</b><br>';
                    @if ($penetapan->lokasi)
                        popupContent += '{{ addslashes($penetapan->lokasi) }}<br>';
                    @endif
                    popupContent += `<small>Lat: ${initialLat.toFixed(6)}, Lng: ${initialLng.toFixed(6)}</small>`;
                    marker.bindPopup(popupContent).openPopup();

                    // Update coordinates on marker drag
                    if (isEditMode) {
                        marker.on('dragend', function(e) {
                            const position = e.target.getLatLng();
                            updateCoordinates(position.lat, position.lng);
                        });
                    }
                }

                // Add click event to map in edit mode
                if (isEditMode) {
                    map.on('click', function(e) {
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;

                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        } else {
                            marker = L.marker([lat, lng], {
                                icon: customIcon,
                                draggable: true
                            }).addTo(map);

                            marker.on('dragend', function(e) {
                                const position = e.target.getLatLng();
                                updateCoordinates(position.lat, position.lng);
                            });
                        }

                        updateCoordinates(lat, lng);
                        marker.bindPopup(
                            `<b>Lokasi Baru</b><br><small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small>`
                            ).openPopup();
                    });
                }
            }

            // Update coordinates in form and display
            function updateCoordinates(lat, lng) {
                $('#inputLatitude').val(lat.toFixed(6));
                $('#inputLongitude').val(lng.toFixed(6));
                $('#displayCoordinates').text(`${lat.toFixed(6)}, ${lng.toFixed(6)}`);
            }

            // Initialize map on page load
            initMap();

            // Toggle Edit Mode
            $('#btnEdit').on('click', function() {
                isEditMode = true;
                $('#viewMode').addClass('d-none');
                $('#editMode').removeClass('d-none');
                $('#btnEdit').addClass('d-none');
                $('#btnSave, #btnCancel').removeClass('d-none');

                // Reinitialize map for edit mode
                setTimeout(() => {
                    initMap();
                }, 100);
            });

            // Cancel Edit
            $('#btnCancel').on('click', function() {
                Swal.fire({
                    title: 'Batalkan Perubahan?',
                    text: 'Perubahan yang Anda buat akan hilang',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        isEditMode = false;
                        $('#editMode').addClass('d-none');
                        $('#viewMode').removeClass('d-none');
                        $('#btnSave, #btnCancel').addClass('d-none');
                        $('#btnEdit').removeClass('d-none');

                        // Reset form
                        $('#formUpdate')[0].reset();

                        // Reinitialize map for view mode
                        setTimeout(() => {
                            initMap();
                        }, 100);
                    }
                });
            });

            // Save Changes
            $('#btnSave').on('click', function() {
                // Validate dates
                const tanggalMulai = $('#inputTanggalMulai').val();
                const tanggalSelesai = $('#inputTanggalSelesai').val();

                if (!tanggalMulai || !tanggalSelesai) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Tanggal mulai dan selesai harus diisi',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Simpan Perubahan?',
                    text: 'Notifikasi akan dikirim ke admin, tim fedora, dan pemohon',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit form
                        $('#formUpdate').submit();
                    }
                });
            });
        });
    </script>
@endpush
