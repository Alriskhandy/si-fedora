<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="/assets/">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ $code ?? 'Error' }} - Terjadi Kesalahan | {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="/assets/css/demo.css" />
</head>

<body>
    <!-- Content -->
    <div class="container-xxl container-p-y">
        <div class="misc-wrapper">
            <h2 class="mb-2 mx-2" style="font-size: 6rem; line-height: 6rem;">
                @if (isset($code) && in_array($code, [500, 502, 503, 504]))
                    500
                @else
                    {{ $code ?? '😵' }}
                @endif
            </h2>
            <h4 class="mb-2 mx-2">
                @if (isset($code) && in_array($code, [500, 502, 503, 504]))
                    Terjadi Kesalahan Server 🔧
                @else
                    Terjadi Kesalahan ⚠️
                @endif
            </h4>
            <p class="mb-4 mx-2">
                @if (isset($code) && in_array($code, [500, 502, 503, 504]))
                    Maaf, terjadi kesalahan pada sistem kami.
                @else
                    Maaf, terjadi kesalahan yang tidak terduga.
                @endif
            </p>
            <p class="mb-4 mx-2 text-muted">
                @if (isset($message))
                    {{ $message }}
                @else
                    Silakan coba lagi atau hubungi administrator jika masalah berlanjut.
                @endif
            </p>
            @if (config('app.debug') && isset($exception))
                <div class="alert alert-danger mx-2 text-start" style="max-width: 600px; margin: 0 auto;">
                    <strong>Debug Info:</strong><br>
                    <small>{{ $exception->getMessage() }}</small>
                </div>
            @endif
            <div class="mt-4">
                <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
                <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="btn btn-primary">
                    <i class="bx bx-home"></i> Ke Beranda
                </a>
            </div>
            <div class="mt-5">
                <img src="/assets/img/illustrations/page-misc-error-light.png" alt="page-misc-error-light"
                    width="500" class="img-fluid" data-app-dark-img="illustrations/page-misc-error-dark.png"
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
