@extends('layouts.app')

@section('title', 'Detail Surat Rekomendasi')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Surat Rekomendasi</h5>
                    <a href="{{ route('surat-rekomendasi.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <!-- Info Surat -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Surat</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nomor Surat</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->nomor_surat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Surat</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->tanggal_surat?->format('d M Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Perihal</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->perihal ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>:</td>
                                    <td>
                                        <span class="badge bg-label-success">Sudah Diterbitkan</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Permohonan</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Kabupaten/Kota</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->kabupatenKota?->getFullNameAttribute() ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Dokumen</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->jenisDokumen?->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Permohonan</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->created_at?->format('d M Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nomor Permohonan</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->nomor_permohonan ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Isi Surat -->
                    <div class="mb-4">
                        <h6>Isi Surat</h6>
                        <div class="card">
                            <div class="card-body">
                                @if($permohonan->isi_surat)
                                    {!! nl2br(e($permohonan->isi_surat)) !!}
                                @else
                                    <span class="text-muted">Isi surat tidak tersedia.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- File Surat -->
                    <div class="mb-4">
                        <h6>File Surat</h6>
                        @if($permohonan->file_surat)
                            <a href="{{ storage_url($permohonan->file_surat) }}" 
                               target="_blank" class="btn btn-primary">
                                <i class="bx bx-download me-1"></i> Download File Surat
                            </a>
                        @else
                            <span class="text-muted">File surat belum diupload.</span>
                        @endif
                    </div>

                    <!-- Tindakan -->
                    <div class="d-flex gap-2">
                        @if($permohonan->file_surat)
                        <a href="{{ storage_url($permohonan->file_surat) }}" 
                           target="_blank" class="btn btn-success">
                            <i class="bx bx-show me-1"></i> Preview Surat
                        </a>
                        @endif
                        <a href="{{ route('surat-rekomendasi.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection