@extends('layouts.app')

@section('title', 'Tambah User')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Manajemen
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
                        <li class="breadcrumb-item active">Tambah Data</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Data</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('users.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="role">Role</label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role"
                                    name="role" required>
                                    <option value="">Pilih Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3" id="kab-kota-container" style="display: none;">
                                <label class="form-label" for="kab_kota">Pilih Kab / Kota</label>
                                <select class="form-select @error('kabupaten_kota_id') is-invalid @enderror" id="kab_kota"
                                    name="kabupaten_kota_id">
                                    <option value="">Pilih Kab / Kota</option>
                                    @foreach ($kab_kota as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('kabupaten_kota_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kabupaten_kota_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="name">Nama Lengkap</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password">Kata Sandi</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bx bx-save me-1"></i> Simpan
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const kabKotaContainer = document.getElementById('kab-kota-container');
            const kabKotaSelect = document.getElementById('kab_kota');

            // Show/hide Kabupaten/Kota based on role selection
            roleSelect.addEventListener('change', function() {
                if (this.value === 'pemohon' ) {
                    kabKotaContainer.style.display = 'block';
                    kabKotaSelect.required = true;
                } else {
                    kabKotaContainer.style.display = 'none';
                    kabKotaSelect.value = '';
                    kabKotaSelect.required = false;
                }
            });

            // Trigger on page load if role already selected
            if (roleSelect.value === 'pemohon' ) {
                kabKotaContainer.style.display = 'block';
                kabKotaSelect.required = true;
            }
        });
    </script>
@endpush
