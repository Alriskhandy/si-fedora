@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Kaban / Penetapan PERDA /</span> Input Penetapan
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
                                        <td width="180">Tindak Lanjut</td>
                                        <td width="10">:</td>
                                        <td>
                                            @if ($permohonan->tindakLanjut)
                                                <span class="badge bg-success">Sudah Upload</span><br>
                                                <small>{{ $permohonan->tindakLanjut->tanggal_upload->format('d M Y') }}</small>
                                            @else
                                                <span class="badge bg-warning">Belum Upload</span>
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
                        <h5 class="mb-0">Input Penetapan PERDA/PERKADA</h5>
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

                        <form action="{{ route('penetapan-perda.store', $permohonan) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="jenis_penetapan">Jenis Penetapan <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('jenis_penetapan') is-invalid @enderror"
                                            id="jenis_penetapan" name="jenis_penetapan" required>
                                            <option value="">Pilih Jenis Penetapan</option>
                                            <option value="perda"
                                                {{ old('jenis_penetapan') == 'perda' ? 'selected' : '' }}>
                                                PERDA (Peraturan Daerah)</option>
                                            <option value="perkada"
                                                {{ old('jenis_penetapan') == 'perkada' ? 'selected' : '' }}>
                                                PERKADA (Peraturan Kepala Daerah)</option>
                                        </select>
                                        @error('jenis_penetapan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="nomor_penetapan">Nomor Penetapan <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('nomor_penetapan') is-invalid @enderror"
                                            id="nomor_penetapan" name="nomor_penetapan"
                                            value="{{ old('nomor_penetapan') }}" placeholder="Contoh: 12 Tahun 2024"
                                            required>
                                        @error('nomor_penetapan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tanggal_penetapan">Tanggal Penetapan <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_penetapan') is-invalid @enderror"
                                    id="tanggal_penetapan" name="tanggal_penetapan"
                                    value="{{ old('tanggal_penetapan', date('Y-m-d')) }}" required>
                                @error('tanggal_penetapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tentang">Tentang <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('tentang') is-invalid @enderror" id="tentang" name="tentang" rows="3"
                                    placeholder="Masukkan judul/tentang dari PERDA/PERKADA" required>{{ old('tentang') }}</textarea>
                                @error('tentang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Contoh: Rencana Kerja Pemerintah Daerah Tahun 2025</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="file_penetapan">File Penetapan (PDF) <span
                                        class="text-danger">*</span></label>
                                <input class="form-control @error('file_penetapan') is-invalid @enderror" type="file"
                                    id="file_penetapan" name="file_penetapan" accept=".pdf" required>
                                @error('file_penetapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: PDF. Maksimal 10MB</small>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="bx bx-info-circle"></i>
                                <strong>Catatan:</strong> Dokumen penetapan ini akan dipublikasikan dan dapat diakses oleh
                                publik.
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Simpan Penetapan
                                </button>
                                <a href="{{ route('penetapan-perda.index') }}" class="btn btn-secondary">
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
