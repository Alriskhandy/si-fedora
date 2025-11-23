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
                            <label class="form-label" for="kode">Kode</label>
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" 
                                   id="kode" name="kode" value="{{ old('kode', $kabupatenKota->kode) }}" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="nama">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" name="nama" value="{{ old('nama', $kabupatenKota->nama) }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="jenis">Jenis</label>
                            <select class="form-select @error('jenis') is-invalid @enderror" 
                                    id="jenis" name="jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="kabupaten" {{ old('jenis', $kabupatenKota->jenis) == 'kabupaten' ? 'selected' : '' }}>Kabupaten</option>
                                <option value="kota" {{ old('jenis', $kabupatenKota->jenis) == 'kota' ? 'selected' : '' }}>Kota</option>
                            </select>
                            @error('jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="kepala_daerah">Kepala Daerah</label>
                            <input type="text" class="form-control @error('kepala_daerah') is-invalid @enderror" 
                                   id="kepala_daerah" name="kepala_daerah" value="{{ old('kepala_daerah', $kabupatenKota->kepala_daerah) }}">
                            @error('kepala_daerah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $kabupatenKota->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="telepon">Telepon</label>
                            <input type="text" class="form-control @error('telepon') is-invalid @enderror" 
                                   id="telepon" name="telepon" value="{{ old('telepon', $kabupatenKota->telepon) }}">
                            @error('telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="alamat">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                      id="alamat" name="alamat" rows="3">{{ old('alamat', $kabupatenKota->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ $kabupatenKota->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Update</button>
                            <a href="{{ route('kabupaten-kota.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection