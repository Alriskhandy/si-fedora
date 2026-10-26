@extends('layouts.app')

@section('title', 'Tambah Role')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tambah Role</h5>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label" for="name">Nama Role</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                        id="name" name="name" value="{{ old('name') }}" required>
                    <div class="form-text">Huruf kecil, angka, dan garis bawah, misalnya <code>opd</code>. Nama tidak dapat diubah setelah dibuat.</div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('pages.admin.roles.partials.checklist', ['checked' => old('permissions', [])])
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection
