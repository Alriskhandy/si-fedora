@extends('layouts.app')

@section('title', 'Edit Kabupaten/Kota')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Kabupaten/Kota</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('kabupaten-kota.update', $kabupatenKota) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label" for="kode">Kode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode') is-invalid @enderror"
                                    id="kode" name="kode" value="{{ old('kode', $kabupatenKota->kode) }}" required>
                                @error('kode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Contoh: 8271 (untuk Kota Ternate)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="nama">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                    id="nama" name="nama" value="{{ old('nama', $kabupatenKota->nama) }}" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Nama tanpa awalan Kabupaten/Kota. Contoh: Ternate, Halmahera
                                    Barat</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="jenis">Jenis <span class="text-danger">*</span></label>
                                <select class="form-select @error('jenis') is-invalid @enderror" id="jenis"
                                    name="jenis" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="kabupaten"
                                        {{ old('jenis', $kabupatenKota->jenis) == 'kabupaten' ? 'selected' : '' }}>Kabupaten
                                    </option>
                                    <option value="kota"
                                        {{ old('jenis', $kabupatenKota->jenis) == 'kota' ? 'selected' : '' }}>Kota</option>
                                </select>
                                @error('jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $kabupatenKota->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-save me-1"></i> Update
                                </button>
                                <a href="{{ route('kabupaten-kota.index') }}" class="btn btn-secondary">
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
