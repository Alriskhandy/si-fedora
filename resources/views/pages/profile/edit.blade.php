@extends('layouts.app')

@section('title', 'Profile')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Profile</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Alert Section -->
        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible" role="alert">
                Profil berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row"
            <!-- Kolom Kiri: Foto Profil & Password -->
            <div class="col-lg-4">
                <!-- Foto Profil -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if (auth()->user()->foto_profile)
                                <img src="{{ asset('storage/' . auth()->user()->foto_profile) }}" alt="Foto Profil"
                                    class="rounded-circle border" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center"
                                    style="width: 150px; height: 150px; font-size: 3rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                        <p class="text-muted mb-1">{{ auth()->user()->email }}</p>
                        @if (auth()->user()->kabupatenKota)
                            <p class="text-muted small">{{ auth()->user()->kabupatenKota->getFullNameAttribute() }}</p>
                        @endif
                    </div>
                    <div class="card-body border-top">
                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('patch')
                            <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                            <div class="mb-3">
                                <label for="foto_update" class="form-label">Upload Foto Baru</label>
                                <input type="file" class="form-control @error('foto_profil') is-invalid @enderror"
                                    id="foto_update" name="foto_profile" accept="image/*">
                                <small class="text-muted">JPG, PNG. Max: 2MB</small>
                                @error('foto_profil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-upload me-1"></i> Ubah Foto
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Panduan Password -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bx bx-shield-quarter text-primary me-1"></i>
                            Panduan Password Yang Baik
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="bx bx-check text-success me-1"></i>
                                Minimal 8 karakter
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check text-success me-1"></i>
                                Kombinasi huruf besar dan kecil
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check text-success me-1"></i>
                                Tambahkan angka
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check text-success me-1"></i>
                                Gunakan simbol khusus (@, #, $, dll)
                            </li>
                            <li class="mb-0">
                                <i class="bx bx-x text-danger me-1"></i>
                                Hindari informasi pribadi (nama, tanggal lahir)
                            </li>
                        </ul>
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
                        @include('pages.profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Update Password</h5>
                        <p class="text-muted small mb-0">Pastikan password Anda aman</p>
                    </div>
                    <div class="card-body">
                        @include('pages.profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
