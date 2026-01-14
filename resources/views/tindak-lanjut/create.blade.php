@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Pemohon / Tindak Lanjut /</span> Upload Laporan
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
                                            <span class="badge bg-success">Hasil Fasilitasi Disetujui</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Upload Laporan Tindak Lanjut</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('tindak-lanjut.store', $permohonan) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="keterangan">Keterangan <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                                    rows="5" placeholder="Masukkan keterangan tindak lanjut hasil fasilitasi..." required>{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Jelaskan tindak lanjut yang telah dilakukan setelah
                                    fasilitasi</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="file_laporan">File Laporan <span
                                        class="text-danger">*</span></label>
                                <input class="form-control @error('file_laporan') is-invalid @enderror" type="file"
                                    id="file_laporan" name="file_laporan" accept=".pdf,.doc,.docx" required>
                                @error('file_laporan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: PDF, DOC, DOCX. Maksimal 10MB</small>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="bx bx-info-circle"></i>
                                <strong>Catatan:</strong> Laporan tindak lanjut ini akan dikirimkan kepada Fasilitator,
                                Admin Peran, dan Kepala Badan untuk ditindaklanjuti.
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-upload"></i> Upload Laporan
                                </button>
                                <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
