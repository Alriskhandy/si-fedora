<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="row">
        <!-- Informasi Dasar -->
        <div class="col-12">
            <h6 class="text-muted mb-3">Informasi Dasar</h6>
        </div>

        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="mt-1">
                    <small class="text-muted">
                        Email belum diverifikasi.
                        <button form="send-verification" class="btn btn-link btn-sm p-0">
                            Kirim ulang verifikasi
                        </button>
                    </small>
                </div>
                @if (session('status') === 'verification-link-sent')
                    <small class="text-success d-block mt-1">Link verifikasi telah dikirim.</small>
                @endif
            @endif
        </div>

        <div class="col-md-6 mb-3">
            <label for="nip" class="form-label">NIP/NIK</label>
            <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip"
                value="{{ old('nip', $user->nip) }}">
            @error('nip')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">No. Telepon</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xx-xxxx-xxxx">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Informasi Instansi -->
        <div class="col-12 mt-3">
            <h6 class="text-muted mb-3">Informasi Instansi</h6>
        </div>

        <div class="col-md-6 mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan"
                name="jabatan" value="{{ old('jabatan', $user->jabatan) }}">
            @error('jabatan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($user->kabupaten_kota_id)
            <div class="col-md-6 mb-3">
                <label for="kabupaten_kota_display" class="form-label">Kabupaten/Kota</label>
                <input type="text" class="form-control" id="kabupaten_kota_display"
                    value="{{ $user->kabupatenKota ? $user->kabupatenKota->getFullNameAttribute() : '-' }}" readonly
                    disabled>
                <small class="text-muted">Hubungi admin untuk mengubah kabupaten/kota</small>
            </div>
        @endif

        <div class="col-12 mb-3">
            <label for="alamat" class="form-label">Alamat Lengkap</label>
            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2">{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary me-2">
            <i class="bx bx-save me-1"></i> Simpan Perubahan
        </button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small">
                <i class="bx bx-check-circle me-1"></i> Tersimpan.
            </span>
        @endif
    </div>
</form>
