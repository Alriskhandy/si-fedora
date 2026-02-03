{{-- Dashboard Default (Role Tidak Dikenali) --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="card-title text-white mb-1">
                            Selamat Datang di SIFEDORA! 👋
                        </h4>
                        <p class="text-white mb-0 opacity-75">
                            Sistem Informasi Fasilitasi Evaluasi Dokumen Perencanaan
                        </p>
                    </div>
                    <div class="d-none d-sm-block">
                        <div class="avatar avatar-xl">
                            <span class="avatar-initial rounded-circle bg-white text-primary">
                                <i class='bx bx-user bx-lg'></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class='bx bx-error-circle bx-lg text-warning mb-3 d-block' style="font-size: 4rem;"></i>
                    <h4 class="mb-2">Role Tidak Dikenali</h4>
                    <p class="text-muted mb-4">Akun Anda belum memiliki role yang sesuai dalam sistem.</p>
                    <div class="alert alert-warning d-inline-block" role="alert">
                        <i class='bx bx-info-circle me-2'></i>
                        Silakan hubungi administrator untuk pengaturan role yang sesuai
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
    </style>
@endpush
