@extends('layouts.app')

@section('title', 'Tambah Tahapan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Tahapan Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('master-tahapan.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="nama_tahapan" class="form-label">Nama Tahapan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_tahapan') is-invalid @enderror"
                                    id="nama_tahapan" name="nama_tahapan" value="{{ old('nama_tahapan') }}" required>
                                @error('nama_tahapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="urutan" class="form-label">Urutan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                    id="urutan" name="urutan" value="{{ old('urutan', 1) }}" min="1" required>
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Urutan tahapan dalam proses (angka lebih kecil akan muncul lebih
                                    awal)</small>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-save me-1"></i> Simpan
                                </button>
                                <a href="{{ route('master-tahapan.index') }}" class="btn btn-secondary">
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
