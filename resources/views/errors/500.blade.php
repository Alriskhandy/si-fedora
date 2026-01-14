@extends('layouts.app')

@push('styles')
    <style>
        .error-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 200px);
        }

        .misc-wrapper {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="error-wrapper">
            <div class="misc-wrapper">
                <div class="error-code">500</div>
                <h4 class="mb-3">Terjadi Kesalahan Server 🔧</h4>
                <p class="mb-4">Maaf, terjadi kesalahan pada sistem kami.</p>
                <p class="text-muted mb-4">
                    Tim teknis kami telah diberitahu dan sedang menangani masalah ini.<br>
                    Silakan coba lagi dalam beberapa saat.
                </p>
                @if (config('app.debug') && isset($exception))
                    <div class="alert alert-danger text-start mb-4">
                        <strong>Debug Info:</strong><br>
                        <small>{{ $exception->getMessage() }}</small>
                    </div>
                @endif
                <div class="mt-4">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="bx bx-home"></i> Ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="bx bx-log-in"></i> Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
<i class="bx bx-home"></i> Ke Beranda
</a>
</div>
<div class="mt-5">
    <img src="/assets/img/illustrations/page-misc-error-light.png" alt="page-misc-error-light" width="500"
        class="img-fluid" data-app-dark-img="illustrations/page-misc-error-dark.png"
        data-app-light-img="illustrations/page-misc-error-light.png" />
</div>
</div>
</div>
<!-- / Content -->

<!-- Core JS -->
<script src="/assets/vendor/libs/jquery/jquery.js"></script>
<script src="/assets/vendor/libs/popper/popper.js"></script>
<script src="/assets/vendor/js/bootstrap.js"></script>
</body>

</html>
