<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="row">
        <div class="col-md-6">

            <div class="mb-3">
                <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                    <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                        id="current_password" name="current_password" autocomplete="current-password">
                    <span class="input-group-text cursor-pointer" onclick="togglePassword('current_password')">
                        <i class="bx bx-hide" id="current_password_icon"></i>
                    </span>
                </div>
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">

            <div class="mb-3">
                <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                    <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                        id="password" name="password" autocomplete="new-password">
                    <span class="input-group-text cursor-pointer" onclick="togglePassword('password')">
                        <i class="bx bx-hide" id="password_icon"></i>
                    </span>
                </div>
                @error('password', 'updatePassword')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password <span
                        class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                    <input type="password"
                        class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                        id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                    <span class="input-group-text cursor-pointer" onclick="togglePassword('password_confirmation')">
                        <i class="bx bx-hide" id="password_confirmation_icon"></i>
                    </span>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-success me-2">
            <i class="bx bx-save me-1"></i> Simpan
        </button>

        @if (session('status') === 'password-updated')
            <span class="text-success small">Tersimpan.</span>
        @endif
    </div>
</form>

<script>
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    } else {
        input.type = 'password';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    }
}
</script>
