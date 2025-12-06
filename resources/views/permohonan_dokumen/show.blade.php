@extends('layouts.app')

@section('title', 'Detail Dokumen Persyaratan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detail Dokumen Persyaratan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Permohonan</strong></td>
                                <td width="5%">:</td>
                                <td>{{ $permohonanDokumen->permohonan->nomor_permohonan ?? '-' }} -
                                    {{ $permohonanDokumen->permohonan->nama_dokumen ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Dokumen</strong></td>
                                <td>:</td>
                                <td>{{ $permohonanDokumen->persyaratanDokumen->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status Dokumen</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($permohonanDokumen->is_ada)
                                        <span class="badge bg-label-success">Dokumen Ada</span>
                                    @else
                                        <span class="badge bg-label-danger">Dokumen Tidak Ada</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>File</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($permohonanDokumen->file_path)
                                        <a href="{{ url('storage/' . $permohonanDokumen->file_path) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download me-1"></i> Lihat File
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            Nama: {{ $permohonanDokumen->file_name }}<br>
                                            Ukuran: {{ $permohonanDokumen->file_size_formatted }}<br>
                                            Tipe: {{ $permohonanDokumen->file_type }}
                                        </small>
                                    @else
                                        <span class="text-muted">Belum diupload</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status Verifikasi</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($permohonanDokumen->status_verifikasi)
                                        <span
                                            class="badge bg-label-{{ $permohonanDokumen->status_verifikasi_badge_class }}">{{ $permohonanDokumen->status_verifikasi_label }}</span>
                                        @if ($permohonanDokumen->catatan_verifikasi)
                                            <br><small class="text-muted">Catatan:
                                                {{ $permohonanDokumen->catatan_verifikasi }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Belum diverifikasi</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td>:</td>
                                <td>{{ $permohonanDokumen->created_at ? $permohonanDokumen->created_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Diupdate</strong></td>
                                <td>:</td>
                                <td>{{ $permohonanDokumen->updated_at ? $permohonanDokumen->updated_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                        </table>

                        <div class="mt-4">
                            @if ($permohonanDokumen->status_verifikasi !== 'verified')
                                <a href="{{ route('permohonan-dokumen.edit', $permohonanDokumen) }}"
                                    class="btn btn-primary me-2">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                            @endif
                            <a href="{{ route('permohonan-dokumen.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
