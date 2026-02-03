<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    <i class="bx bx-trash me-1"></i> Hapus Akun
</button>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title">Hapus Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Apakah Anda yakin ingin menghapus akun Anda?
                    </p>
                    <p class="text-muted small mb-3">
                        Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen.
                        Masukkan password Anda untuk mengkonfirmasi penghapusan akun.
                    </p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Password <span
                                class="text-danger">*</span></label>
                        <input type="password"
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                            id="delete_password" name="password" placeholder="Password" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
            modal.show();
        });
    </script>
@endif
