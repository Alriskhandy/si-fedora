@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Pemohon / Tindak Lanjut /</span> Detail Laporan
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                        <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="180">Nomor Permohonan</td>
                                        <td width="10">:</td>
                                        <td><strong>{{ $permohonan->nomor_permohonan }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Kabupaten/Kota</td>
                                        <td>:</td>
                                        <td>{{ $permohonan->kabupatenKota->nama_kabkota ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Perihal</td>
                                        <td>:</td>
                                        <td>{{ $permohonan->perihal }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="180">Tanggal Permohonan</td>
                                        <td width="10">:</td>
                                        <td>{{ $permohonan->created_at->format('d F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td>:</td>
                                        <td>
                                            <span class="badge bg-success">Tindak Lanjut Sudah Diupload</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Tindak Lanjut</h5>
                        <a href="{{ route('tindak-lanjut.download', $permohonan) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-download"></i> Download Laporan
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($tindakLanjut)
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tanggal Upload</label>
                                <p>{{ $tindakLanjut->tanggal_upload->format('d F Y H:i:s') }} WIB</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Diupload Oleh</label>
                                <p>{{ $tindakLanjut->uploader->name ?? '-' }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Keterangan</label>
                                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                    {!! nl2br(e($tindakLanjut->keterangan)) !!}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">File Laporan</label>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-file" style="font-size: 24px;"></i>
                                    <div>
                                        <p class="mb-0">
                                            {{ basename($tindakLanjut->file_laporan) }}
                                        </p>
                                        @if (Storage::exists($tindakLanjut->file_laporan))
                                            <small class="text-muted">
                                                Size:
                                                {{ number_format(Storage::size($tindakLanjut->file_laporan) / 1024 / 1024, 2) }}
                                                MB
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-success" role="alert">
                                <i class="bx bx-check-circle"></i>
                                Laporan tindak lanjut telah berhasil diupload dan akan ditindaklanjuti oleh tim terkait.
                            </div>
                        @else
                            <div class="alert alert-warning" role="alert">
                                <i class="bx bx-info-circle"></i>
                                Tindak lanjut belum diupload.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
