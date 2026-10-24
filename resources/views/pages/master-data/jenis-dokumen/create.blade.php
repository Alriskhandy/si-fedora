@extends('layouts.app')

@section('title', 'Tambah Jenis Dokumen')

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
                        <li class="breadcrumb-item"><a href="{{ route('master-jenis-dokumen.index') }}">Master Jenis Dokumen</a></li>
                        <li class="breadcrumb-item active">Tambah Data</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('master-jenis-dokumen.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Data</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('master-jenis-dokumen.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="nama">Nama Jenis Dokumen <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                    id="nama" name="nama" value="{{ old('nama') }}" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                        value="1" {{ old('status', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">Status Aktif</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bx bx-save me-1"></i> Simpan
                                </button>
                                <a href="{{ route('master-jenis-dokumen.index') }}" class="btn btn-secondary">
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
