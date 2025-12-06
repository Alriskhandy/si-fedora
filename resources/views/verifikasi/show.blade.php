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
        <form action="{{ route('verifikasi.verifikasi', $permohonan) }}" method="POST">
            @csrf

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
                                        <th>Nama Dokumen</th>
                                        <th width="20%">File</th>
                                        <th width="15%">Status Upload</th>
                                        <th width="20%">Status Verifikasi</th>
                                        <th width="25%">Catatan</th>
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
                                        <td>
                                            @if ($suratPermohonan->file_path)
                                                <a href="{{ asset('storage/' . $suratPermohonan->file_path) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-download"></i> Lihat
                                                </a>
                                                <br><small class="text-muted">{{ $suratPermohonan->file_name }}</small>
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
                                            <select class="form-select form-select-sm"
                                                name="dokumen[{{ $suratPermohonan->id }}][status_verifikasi]" required>
                                                <option value="verified"
                                                    {{ $suratPermohonan->status_verifikasi === 'verified' ? 'selected' : '' }}>
                                                    ✓ Sesuai</option>
                                                <option value="revision_required"
                                                    {{ $suratPermohonan->status_verifikasi === 'revision_required' ? 'selected' : '' }}>
                                                    ✗ Revisi</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm" name="dokumen[{{ $suratPermohonan->id }}][catatan]" rows="2"
                                                placeholder="Catatan...">{{ $suratPermohonan->catatan_verifikasi }}</textarea>
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
                                    <th width="5%">No</th>
                                    <th>Nama Dokumen</th>
                                    <th width="20%">File</th>
                                    <th width="10%">Status</th>
                                    <th width="20%">Verifikasi</th>
                                    <th width="25%">Catatan</th>
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
                                        <td>
                                            @if ($dokumen->file_path)
                                                <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-download"></i> Lihat
                                                </a>
                                                <br><small class="text-muted">{{ $dokumen->file_name }}</small>
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
                                            <select class="form-select form-select-sm"
                                                name="dokumen[{{ $dokumen->id }}][status_verifikasi]" required>
                                                <option value="verified"
                                                    {{ $dokumen->status_verifikasi === 'verified' ? 'selected' : '' }}>✓
                                                    Sesuai</option>
                                                <option value="revision_required"
                                                    {{ $dokumen->status_verifikasi === 'revision_required' ? 'selected' : '' }}>
                                                    ✗ Revisi</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm" name="dokumen[{{ $dokumen->id }}][catatan]" rows="2"
                                                placeholder="Catatan...">{{ $dokumen->catatan_verifikasi }}</textarea>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
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

            <!-- Kesimpulan -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-check-shield me-2'></i>Kesimpulan Verifikasi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status_verifikasi">
                                <strong>Status Verifikasi Akhir</strong> <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="status_verifikasi" name="status_verifikasi" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="verified">✓ Dokumen LENGKAP dan SESUAI</option>
                                <option value="revision_required">✗ Dokumen Perlu REVISI</option>
                            </select>
                            <div class="form-text">
                                <i class='bx bx-info-circle'></i> Pilih "Perlu REVISI" jika ada dokumen yang tidak lengkap.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="catatan_umum">Catatan Umum</label>
                            <textarea class="form-control" id="catatan_umum" name="catatan_umum" rows="4"
                                placeholder="Catatan umum hasil verifikasi...">{{ old('catatan_umum') }}</textarea>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class='bx bx-info-circle me-2'></i>
                        <strong>Panduan:</strong> Periksa semua dokumen, pastikan format dan isi sesuai persyaratan. Berikan
                        catatan jelas jika perlu revisi.
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('verifikasi.index') }}" class="btn btn-outline-secondary">
                    <i class='bx bx-x me-1'></i> Batal
                </a>
                <button type="submit" class="btn btn-success"
                    onclick="return confirm('Apakah hasil verifikasi sudah benar?')">
                    <i class='bx bx-check-circle me-1'></i> Simpan Hasil Verifikasi
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
    </style>
@endpush
