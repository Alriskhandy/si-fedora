@extends('layouts.app')

@section('title', 'Edit Urusan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Master Data
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('master-urusan.index') }}">Master Urusan</a></li>
                        <li class="breadcrumb-item active">Edit Data</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('master-urusan.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Data</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('master-urusan.update', $masterUrusan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="nama_urusan" class="form-label">Nama Urusan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_urusan') is-invalid @enderror"
                                    id="nama_urusan" name="nama_urusan"
                                    value="{{ old('nama_urusan', $masterUrusan->nama_urusan) }}" required>
                                @error('nama_urusan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('kategori') is-invalid @enderror" id="kategori"
                                    name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="wajib_dasar"
                                        {{ old('kategori', $masterUrusan->kategori) == 'wajib_dasar' ? 'selected' : '' }}>
                                        Urusan Wajib Pelayanan Dasar
                                    </option>
                                    <option value="wajib_non_dasar"
                                        {{ old('kategori', $masterUrusan->kategori) == 'wajib_non_dasar' ? 'selected' : '' }}>
                                        Urusan Wajib Non Pelayanan Dasar
                                    </option>
                                    <option value="pilihan"
                                        {{ old('kategori', $masterUrusan->kategori) == 'pilihan' ? 'selected' : '' }}>
                                        Urusan Pilihan
                                    </option>
                                </select>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="urutan" class="form-label">Urutan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                    id="urutan" name="urutan" value="{{ old('urutan', $masterUrusan->urutan) }}"
                                    min="1" required>
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Urutan urusan dalam kategori (angka lebih kecil akan muncul lebih
                                    awal)</small>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bx bx-save me-1"></i> Simpan
                                </button>
                                <a href="{{ route('master-urusan.index') }}" class="btn btn-secondary">
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
