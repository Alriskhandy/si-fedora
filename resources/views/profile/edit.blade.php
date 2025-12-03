@extends('layouts.app')

@section('title', 'Profile')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- Kolom Kiri: Foto Profil & Password -->
            <div class="col-lg-4">
                <!-- Foto Profil -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if (auth()->user()->foto)
                                <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Foto Profil"
                                    class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center"
                                    style="width: 150px; height: 150px; font-size: 3rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                        <p class="text-muted mb-1">{{ auth()->user()->email }}</p>
                        @if (auth()->user()->jabatan)
                            <p class="text-muted small mb-0">{{ auth()->user()->jabatan }}</p>
                        @endif
                        @if (auth()->user()->kabupatenKota)
                            <p class="text-muted small">{{ auth()->user()->kabupatenKota->getFullNameAttribute() }}</p>
                        @endif
                    </div>
                    <div class="card-body border-top">
                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('patch')
                            <div class="mb-3">
                                <label for="foto_update" class="form-label">Upload Foto Baru</label>
                                <input type="file" class="form-control @error('foto') is-invalid @enderror"
                                    id="foto_update" name="foto" accept="image/*">
                                <small class="text-muted">JPG, PNG. Max: 2MB</small>
                                @error('foto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-upload me-1"></i> Update Foto
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Update Password -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Update Password</h5>
                        <p class="text-muted small mb-0">Pastikan password Anda aman</p>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Hapus Akun</h5>
                        <p class="text-muted small mb-0">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Informasi Profile -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Profile</h5>
                        <p class="text-muted small mb-0">Update informasi pribadi dan kontak Anda</p>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
