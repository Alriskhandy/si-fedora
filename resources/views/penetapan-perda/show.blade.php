@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Kaban / Penetapan PERDA /</span> Detail Penetapan
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                        <a href="{{ route('penetapan-perda.index') }}" class="btn btn-sm btn-secondary">
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
                                            <span class="badge bg-success">Sudah Ditetapkan</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Penetapan</h5>
                        <a href="{{ route('penetapan-perda.download', $permohonan) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-download"></i> Download File
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($penetapan)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Nomor Penetapan</label>
                                        <p class="fs-5">{{ $penetapan->nomor_perda }}</p>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Tanggal Penetapan</label>
                                        <p>{{ \Carbon\Carbon::parse($penetapan->tanggal_penetapan)->format('d F Y') }}</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Ditetapkan Oleh</label>
                                        <p>{{ $penetapan->pembuat->name ?? '-' }}</p>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Tanggal Input</label>
                                        <p>{{ $penetapan->created_at->format('d F Y H:i:s') }} WIB</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Keterangan</label>
                                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                    {!! nl2br(e($penetapan->keterangan)) !!}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">File Penetapan</label>
                                <div class="d-flex align-items-center gap-2 border rounded p-3"
                                    style="background-color: #fff3cd;">
                                    <i class="bx bxs-file-pdf text-danger" style="font-size: 32px;"></i>
                                    <div>
                                        <p class="mb-0 fw-bold">
                                            {{ basename($penetapan->file_perda) }}
                                        </p>
                                        @if (Storage::exists('public/' . $penetapan->file_perda))
                                            <small class="text-muted">
                                                Size:
                                                {{ number_format(Storage::size('public/' . $penetapan->file_perda) / 1024 / 1024, 2) }}
                                                MB
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-success" role="alert">
                                <i class="bx bx-check-circle"></i>
                                Dokumen penetapan ini telah dipublikasikan dan dapat diakses oleh publik melalui menu
                                Dokumen PERDA/PERKADA.
                            </div>
                        @else
                            <div class="alert alert-warning" role="alert">
                                <i class="bx bx-info-circle"></i>
                                Penetapan belum diinput.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
