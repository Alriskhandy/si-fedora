@extends('layouts.app')

@section('title', 'Ajukan Perpanjangan Waktu')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('permohonan.show', $permohonan) }}">Detail Permohonan</a>
                    </li>
                    <li class="breadcrumb-item active">Ajukan Perpanjangan</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Ajukan Perpanjangan Waktu Upload</h4>
            <p class="text-muted mb-0">Untuk Permohonan: <strong>{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong></p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class='bx bx-edit me-2'></i>Form Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('perpanjangan-waktu.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="permohonan_id" value="{{ $permohonan->id }}">

                            <div class="mb-3">
                                <label class="form-label">Alasan Perpanjangan <span class="text-danger">*</span></label>
                                <textarea name="alasan" class="form-control @error('alasan') is-invalid @enderror" rows="5" required
                                    placeholder="Jelaskan alasan mengapa memerlukan perpanjangan waktu (minimal 20 karakter)">{{ old('alasan') }}</textarea>
                                <small class="text-muted">Berikan penjelasan yang detail dan jelas</small>
                                @error('alasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah Hari Perpanjangan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="perpanjangan_hari"
                                        class="form-control @error('perpanjangan_hari') is-invalid @enderror"
                                        value="{{ old('perpanjangan_hari', 7) }}" min="1" max="30" required>
                                    <span class="input-group-text">Hari</span>
                                    @error('perpanjangan_hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Minimal 1 hari, maksimal 30 hari</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Surat Permohonan <span class="text-danger">*</span></label>
                                <input type="file" name="surat_permohonan"
                                    class="form-control @error('surat_permohonan') is-invalid @enderror" accept=".pdf"
                                    required>
                                <small class="text-muted">Upload surat permohonan resmi dalam format PDF (max 10MB)</small>
                                @error('surat_permohonan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class='bx bx-info-circle me-2'></i>
                                <strong>Catatan:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Permohonan akan diproses maksimal 2 hari kerja</li>
                                    <li>Surat permohonan harus resmi dan ditandatangani</li>
                                    <li>Jika disetujui, batas waktu upload akan otomatis diperpanjang</li>
                                </ul>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class='bx bx-send'></i> Ajukan Permohonan
                                </button>
                                <a href="{{ route('permohonan.show', $permohonan) }}" class="btn btn-outline-secondary">
                                    <i class='bx bx-x'></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Informasi Permohonan</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="45%">Kabupaten/Kota:</th>
                                <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tahun:</th>
                                <td>{{ $permohonan->tahun }}</td>
                            </tr>
                            <tr>
                                <th>Batas Upload Saat Ini:</th>
                                <td>
                                    @if ($permohonan->jadwalFasilitasi)
                                        <strong class="text-danger">
                                            {{ \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->batas_permohonan)->format('d M Y, H:i') }}
                                            WIB
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            Tersisa
                                            {{ now()->diffInDays($permohonan->jadwalFasilitasi->batas_permohonan) }} hari
                                        </small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Panduan -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class='bx bx-help-circle me-1'></i>Panduan</h6>
                    </div>
                    <div class="card-body">
                        <h6>Syarat Permohonan:</h6>
                        <ol class="small mb-3">
                            <li>Alasan yang jelas dan dapat dipertanggungjawabkan</li>
                            <li>Surat permohonan resmi dari Kabupaten/Kota</li>
                            <li>Jumlah hari perpanjangan yang wajar (max 30 hari)</li>
                        </ol>

                        <h6>Proses Persetujuan:</h6>
                        <ol class="small mb-0">
                            <li>Permohonan akan direview oleh admin</li>
                            <li>Keputusan akan diinformasikan via email dan notifikasi sistem</li>
                            <li>Jika disetujui, batas waktu akan otomatis diupdate</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
