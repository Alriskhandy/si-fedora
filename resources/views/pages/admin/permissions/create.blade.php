@extends('layouts.app')

@section('title', 'Tambah Permission')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Permission Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label" for="name">Nama Permission</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            <div class="form-text">Contoh: user.view, permohonan.create, dll</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Simpan</button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection