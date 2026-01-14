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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
                <div class="error-code">404</div>
                <h4 class="mb-3">Halaman Tidak Ditemukan ⚠️</h4>
                <p class="mb-4">Oops! Halaman yang Anda cari tidak ditemukan.</p>
                <p class="text-muted mb-4">
                    {{ $exception->getMessage() ?: 'URL yang Anda akses mungkin salah atau halaman telah dipindahkan.' }}
                </p>
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
<script src="/assets/vendor/libs/jquery/jquery.js"></script>
<script src="/assets/vendor/libs/popper/popper.js"></script>
<script src="/assets/vendor/js/bootstrap.js"></script>
</body>

</html>
