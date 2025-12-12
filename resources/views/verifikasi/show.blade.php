@extends('layouts.app')

@section('title', 'Verifikasi Permohonan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Verifikasi Permohonan</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('verifikasi.index') }}">Verifikasi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('verifikasi.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Informasi Permohonan -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-info-circle me-2'></i>Informasi Permohonan
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Kabupaten/Kota</strong></td>
                                <td width="5%">:</td>
                                <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Dokumen</strong></td>
                                <td>:</td>
                                <td><span class="badge bg-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Tahun</strong></td>
                                <td>:</td>
                                <td>{{ $permohonan->tahun }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Tanggal Pengajuan</strong></td>
                                <td width="5%">:</td>
                                <td>{{ $permohonan->submitted_at ? $permohonan->submitted_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>:</td>
                                <td><span
                                        class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Jadwal Fasilitasi</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($permohonan->jadwalFasilitasi)
                                        {{ $permohonan->jadwalFasilitasi->tanggal_mulai->format('d M Y') }} -
                                        {{ $permohonan->jadwalFasilitasi->tanggal_selesai->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Verifikasi -->
        @php
            $suratPermohonan = $permohonan->permohonanDokumen->first(function ($dok) {
                return $dok->masterKelengkapan && $dok->masterKelengkapan->kategori === 'surat_permohonan';
            });

            $kelengkapanVerifikasi = $permohonan->permohonanDokumen->filter(function ($dok) {
                return $dok->masterKelengkapan && $dok->masterKelengkapan->kategori === 'kelengkapan_verifikasi';
            });
        @endphp

        <!-- Surat Permohonan -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class='bx bx-file-blank me-2'></i>Surat Permohonan
                </h5>
                <span class="badge bg-label-danger">Wajib</span>
            </div>
            <div class="card-body">
                @if ($suratPermohonan)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="28%">Nama Dokumen</th>
                                    <th width="10%" class="text-center">File</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="15%">Verifikasi</th>
                                    <th width="27%">Catatan</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>{{ $suratPermohonan->masterKelengkapan->nama_dokumen ?? 'Surat Permohonan' }}</strong>
                                        @if ($suratPermohonan->masterKelengkapan && $suratPermohonan->masterKelengkapan->deskripsi)
                                            <br><small
                                                class="text-muted">{{ $suratPermohonan->masterKelengkapan->deskripsi }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($suratPermohonan->file_path)
                                            <a href="{{ asset('storage/' . $suratPermohonan->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-download"></i> Lihat
                                            </a>
                                        @else
                                            <span class="badge bg-label-danger">Belum upload</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($suratPermohonan->is_ada)
                                            <span class="badge bg-label-success"><i class='bx bx-check'></i> Ada</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class='bx bx-x'></i> Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm verifikasi-status"
                                            data-dokumen-id="{{ $suratPermohonan->id }}"
                                            {{ $suratPermohonan->status_verifikasi === 'verified' ? 'disabled' : '' }}>
                                            <option value="pending"
                                                {{ $suratPermohonan->status_verifikasi === 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="verified"
                                                {{ $suratPermohonan->status_verifikasi === 'verified' ? 'selected' : '' }}>
                                                ✓ Sesuai</option>
                                            <option value="revision"
                                                {{ $suratPermohonan->status_verifikasi === 'revision' ? 'selected' : '' }}>
                                                ✗ Revisi</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea class="form-control form-control-sm catatan-verifikasi" data-dokumen-id="{{ $suratPermohonan->id }}"
                                            rows="2" placeholder="Catatan..." {{ $suratPermohonan->status_verifikasi === 'verified' ? 'disabled' : '' }}>{{ $suratPermohonan->catatan_verifikasi }}</textarea>
                                    </td>
                                    <td class="text-center">
                                        @if ($suratPermohonan->status_verifikasi === 'verified')
                                            <span class="badge bg-success"><i class='bx bx-check-circle'></i> Selesai</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary btn-verifikasi"
                                                data-dokumen-id="{{ $suratPermohonan->id }}">
                                                <i class='bx bx-save'></i> Simpan
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class='bx bx-error-circle me-2'></i>Surat permohonan belum tersedia.
                    </div>
                @endif
            </div>
        </div>

        <!-- Kelengkapan Verifikasi -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-folder-open me-2'></i>Kelengkapan Verifikasi
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="26%">Nama Dokumen</th>
                                <th width="10%" class="text-center">File</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="15%">Verifikasi</th>
                                <th width="24%">Catatan</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelengkapanVerifikasi as $index => $dokumen)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen Kelengkapan' }}</strong>
                                        @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->wajib)
                                            <span class="badge badge-sm bg-label-danger ms-1">Wajib</span>
                                        @endif
                                        @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->deskripsi)
                                            <br><small
                                                class="text-muted">{{ $dokumen->masterKelengkapan->deskripsi }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($dokumen->file_path)
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-download"></i> Lihat
                                            </a>
                                        @else
                                            <span class="badge bg-label-danger">Belum upload</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($dokumen->is_ada)
                                            <span class="badge bg-label-success"><i class='bx bx-check'></i> Ada</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class='bx bx-x'></i> Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm verifikasi-status"
                                            data-dokumen-id="{{ $dokumen->id }}"
                                            {{ $dokumen->status_verifikasi === 'verified' ? 'disabled' : '' }}>
                                            <option value="pending"
                                                {{ $dokumen->status_verifikasi === 'pending' ? 'selected' : '' }}>Pending
                                            </option>
                                            <option value="verified"
                                                {{ $dokumen->status_verifikasi === 'verified' ? 'selected' : '' }}>✓
                                                Sesuai</option>
                                            <option value="revision"
                                                {{ $dokumen->status_verifikasi === 'revision' ? 'selected' : '' }}>
                                                ✗ Revisi</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea class="form-control form-control-sm catatan-verifikasi" data-dokumen-id="{{ $dokumen->id }}"
                                            rows="2" placeholder="Catatan..." {{ $dokumen->status_verifikasi === 'verified' ? 'disabled' : '' }}>{{ $dokumen->catatan_verifikasi }}</textarea>
                                    </td>
                                    <td class="text-center">
                                        @if ($dokumen->status_verifikasi === 'verified')
                                            <span class="badge bg-success"><i class='bx bx-check-circle'></i>
                                                Selesai</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary btn-verifikasi"
                                                data-dokumen-id="{{ $dokumen->id }}">
                                                <i class='bx bx-save'></i> Simpan
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class='bx bx-folder-open bx-lg text-muted mb-2 d-block'></i>
                                        <p class="text-muted mb-0">Tidak ada dokumen kelengkapan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Panduan:</strong> Periksa dan verifikasi setiap dokumen satu per satu. Jika dokumen perlu revisi,
            pemohon akan diminta mengupload ulang dokumen tersebut.
        </div>

        <!-- Tombol Kembali -->
        <div class="text-start">
            <a href="{{ route('verifikasi.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle verifikasi per dokumen
            $('.btn-verifikasi').on('click', function() {
                const button = $(this);
                const dokumenId = button.data('dokumen-id');
                const status = $('.verifikasi-status[data-dokumen-id="' + dokumenId + '"]').val();
                const catatan = $('.catatan-verifikasi[data-dokumen-id="' + dokumenId + '"]').val();
                const buttonText = button.html();

                // Validasi
                if (!status || status === 'pending') {
                    alert('Silakan pilih status verifikasi terlebih dahulu');
                    return;
                }

                if (status === 'revision' && !catatan.trim()) {
                    alert('Catatan wajib diisi jika dokumen perlu revisi');
                    return;
                }

                // Disable button dan show loading
                button.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Menyimpan...');

                // Submit via AJAX
                $.ajax({
                    url: '{{ route('verifikasi.verifikasi-dokumen', $permohonan) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        dokumen_id: dokumenId,
                        status_verifikasi: status,
                        catatan: catatan
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success
                            button.removeClass('btn-primary').addClass('btn-success').html(
                                '<i class="bx bx-check-circle"></i> Tersimpan'
                            );

                            // Reload after 1 second
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        // Show error
                        button.removeClass('btn-primary').addClass('btn-danger').html(
                            '<i class="bx bx-x-circle"></i> Gagal'
                        );

                        // Reset after 2 seconds
                        setTimeout(function() {
                            button.prop('disabled', false)
                                .removeClass('btn-danger')
                                .addClass('btn-primary')
                                .html(buttonText);
                        }, 2000);

                        let errorMessage = 'Terjadi kesalahan saat menyimpan verifikasi';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        console.error('Error:', errorMessage);
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
@endpush
