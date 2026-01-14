@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Kaban / Surat Penyampaian /</span> Upload Surat
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                        <a href="{{ route('surat-penyampaian-hasil.index') }}" class="btn btn-sm btn-secondary">
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
                                        <td width="180">Hasil Fasilitasi</td>
                                        <td width="10">:</td>
                                        <td>
                                            @if ($hasilFasilitasi->draft_file)
                                                <span class="badge bg-info">Draft tersedia</span>
                                            @endif
                                            @if ($hasilFasilitasi->final_file)
                                                <span class="badge bg-success">PDF tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Upload Surat Penyampaian Hasil Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('surat-penyampaian-hasil.store', $permohonan) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="surat_penyampaian">Surat Penyampaian (PDF) <span
                                        class="text-danger">*</span></label>
                                <input class="form-control @error('surat_penyampaian') is-invalid @enderror" type="file"
                                    id="surat_penyampaian" name="surat_penyampaian" accept=".pdf" required>
                                @error('surat_penyampaian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: PDF. Maksimal 10MB</small>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="bx bx-info-circle"></i>
                                <strong>Catatan:</strong> Surat penyampaian ini akan dapat diunduh oleh semua pengguna
                                sistem
                                dan pemohon dapat melanjutkan ke tahap upload tindak lanjut setelah surat ini diupload.
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-upload"></i> Upload Surat
                                </button>
                                <a href="{{ route('surat-penyampaian-hasil.index') }}" class="btn btn-secondary">
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
