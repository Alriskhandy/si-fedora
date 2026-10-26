@extends('layouts.app')

@section('title', 'Atur Hak Akses Role')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Hak Akses Role: {{ $role->name }}</h5>
                    <small class="text-muted">Perubahan langsung berlaku bagi semua pengguna dengan role ini dan tercatat di activity log.</small>
                </div>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body">
                @include('pages.admin.roles.partials.checklist', ['checked' => old('permissions', $rolePermissions)])
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection
