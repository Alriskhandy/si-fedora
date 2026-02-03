@extends('layouts.app')

@section('title', 'Tambah Kelengkapan Verifikasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Kelengkapan Verifikasi Baru</h5>
                        <p class="card-text text-muted mb-0">Tambahkan dokumen kelengkapan yang dibutuhkan untuk proses
                            verifikasi</p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('master-kelengkapan.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="jenis_dokumen_id" class="form-label">Jenis Dokumen <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('jenis_dokumen_id') is-invalid @enderror"
                                        id="jenis_dokumen_id" name="jenis_dokumen_id" required>
                                        <option value="">Pilih Jenis Dokumen</option>
                                        @foreach ($jenisDokumen as $jd)
                                            <option value="{{ $jd->id }}"
                                                {{ old('jenis_dokumen_id') == $jd->id ? 'selected' : '' }}>
                                                {{ $jd->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis_dokumen_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nama_dokumen" class="form-label">Nama Dokumen <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_dokumen') is-invalid @enderror"
                                    id="nama_dokumen" name="nama_dokumen" value="{{ old('nama_dokumen') }}"
                                    placeholder="Contoh: Surat Permohonan Fasilitasi dari Bupati/Walikota" required>
                                @error('nama_dokumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3"
                                    placeholder="Penjelasan detail mengenai dokumen ini...">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="wajib" class="form-label">Status Kelengkapan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('wajib') is-invalid @enderror" id="wajib"
                                        name="wajib" required>
                                        <option value="">Pilih Status</option>
                                        <option value="1" {{ old('wajib') == '1' ? 'selected' : '' }}>Wajib</option>
                                        <option value="0" {{ old('wajib') == '0' ? 'selected' : '' }}>Opsional
                                        </option>
                                    </select>
                                    @error('wajib')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="urutan" class="form-label">Urutan</label>
                                    <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                        id="urutan" name="urutan" value="{{ old('urutan', 1) }}" min="1">
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Urutan tampilan dokumen</small>
                                </div>
                            </div>

                            <div class="mb-3 mt-3 pt-3 border-top">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-save me-1"></i> Simpan
                                </button>
                                <a href="{{ route('master-kelengkapan.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
