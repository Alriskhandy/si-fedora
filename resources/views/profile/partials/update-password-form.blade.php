<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
        <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
            id="current_password" name="current_password" autocomplete="current-password">
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
        <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
            id="password" name="password" autocomplete="new-password">
        @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password <span
                class="text-danger">*</span></label>
        <input type="password"
            class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
            id="password_confirmation" name="password_confirmation" autocomplete="new-password">
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-primary me-2">
            <i class="bx bx-save me-1"></i> Simpan
        </button>

        @if (session('status') === 'password-updated')
            <span class="text-success small">Tersimpan.</span>
        @endif
    </div>
</form>
