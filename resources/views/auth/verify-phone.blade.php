<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="Sistem Informasi Fedora - Verifikasi Nomor HP" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi Nomor HP - SIFEDORA</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    {{-- SIFEDORA Custom Theme --}}
    <link rel="stylesheet" href="{{ asset('assets/css/sifedora-theme.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        :root {
            --primary-color: #696cff;
            --primary-hover: #5f61e6;
        }

        body {
            transition: background-color 0.3s ease, color 0.3s ease;
            position: relative;
            overflow-x: hidden;
        }

        /* Light Mode (Default) */
        body:not(.dark-mode) {
            background-color: #cfecf7;
        }

        /* Dark Mode */
        body.dark-mode {
            background: linear-gradient(135deg, #0f1729 0%, #1a2642 50%, #0a1128 100%);
            color: #b4bdc6;
        }

        body.dark-mode .card {
            background-color: #1a2642;
            border: 1px solid #2d3a54;
        }

        body.dark-mode h5,
        body.dark-mode .form-label {
            color: #fff;
        }

        body.dark-mode p,
        body.dark-mode small {
            color: #b4bdc6;
        }

        body.dark-mode .form-control {
            background-color: #2d3a54 !important;
            border-color: #3d4a64 !important;
            color: #ffffff !important;
        }

        body.dark-mode .form-control:focus {
            border-color: #4dabf7 !important;
            box-shadow: 0 0 0 0.2rem rgba(77, 171, 247, 0.25) !important;
            background-color: #2d3a54 !important;
            color: #ffffff !important;
        }

        body.dark-mode .form-control::placeholder {
            color: #8a95a5 !important;
        }

        body.dark-mode .input-group-text {
            background-color: #2d3a54 !important;
            border-color: #3d4a64 !important;
            color: #ffffff !important;
        }

        body.dark-mode a {
            color: #4dabf7;
        }

        body.dark-mode a:hover {
            color: #339af0;
        }

        body.dark-mode .btn-primary {
            background: linear-gradient(135deg, #4dabf7 0%, #339af0 100%);
            border-color: #4dabf7;
        }

        body.dark-mode .btn-primary:hover {
            box-shadow: 0 8px 20px rgba(77, 171, 247, 0.4);
        }

        body.dark-mode .alert-success {
            background-color: #0a3622;
            color: #75dda7;
            border-color: #145a37;
        }

        body.dark-mode .alert-danger {
            background-color: #3d1319;
            color: #ea868f;
            border-color: #6d232e;
        }

        body.dark-mode .alert-info {
            background-color: #0a2540;
            color: #4dabf7;
            border-color: #1e4976;
        }

        body.dark-mode .invalid-feedback {
            color: #ea868f;
        }

        body.dark-mode .text-muted {
            color: #8a95a5 !important;
        }

        /* Animated Background Particles */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
            opacity: 0.15;
        }

        body:not(.dark-mode) .particle {
            background: rgba(105, 108, 255, 0.3);
        }

        body.dark-mode .particle {
            background: rgba(77, 171, 247, 0.3);
            opacity: 0.2;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) translateX(0) scale(1);
            }

            25% {
                transform: translateY(-50px) translateX(30px) scale(1.1);
            }

            50% {
                transform: translateY(-100px) translateX(-30px) scale(0.9);
            }

            75% {
                transform: translateY(-50px) translateX(30px) scale(1.05);
            }
        }

        /* Theme Toggle Button */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            color: #696cff;
        }

        body.dark-mode .theme-toggle {
            background: #1a2642;
            color: #4dabf7;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(15deg);
        }

        .theme-toggle:active {
            transform: scale(0.95);
        }

        /* Card Animations */
        .authentication-inner {
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        body.dark-mode .card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        /* Logo Animation */
        .app-brand-logo img {
            transition: transform 0.3s ease;
        }

        .app-brand-link:hover .app-brand-logo img {
            transform: scale(1.05) rotate(2deg);
        }

        /* Elements Fade In */
        .app-brand {
            animation: fadeIn 0.8s ease-out 0.2s both;
        }

        h5 {
            animation: fadeIn 0.8s ease-out 0.3s both;
        }

        p.mb-4 {
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .mb-3 {
            animation: fadeIn 0.8s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Button Ripple Effect */
        .btn {
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(105, 108, 255, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Input Focus Animation */
        .form-control,
        .input-group {
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .form-control:focus {
            transform: scale(1.01);
        }

        /* Alert Slide Down */
        .alert {
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Phone Icon */
        .phone-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #32e39f 0%, #46f033 100%);
            border-radius: 50%;
            font-size: 2.5rem;
            color: white;
            animation: pulse 2s infinite;
        }

        body.dark-mode .phone-icon {
            background: linear-gradient(135deg, #32e39f 0%, #46f033 100%);
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(105, 255, 175, 0.7);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(105, 108, 255, 0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .theme-toggle {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background -->
    <div class="bg-animation" id="bgAnimation"></div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
        <i class='bx bx-moon'></i>
    </button>

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                        <div class="phone-icon">
                            <i class='bx bxl-whatsapp'></i>
                        </div>

                        <h5 class="mb-2 text-center">Verifikasi Nomor WhatsApp</h5>
                        <p class="mb-4 text-center">Masukkan nomor WhatsApp Anda untuk menerima kode verifikasi</p>

                        @if (session('status'))
                            <div class="alert alert-success mb-3" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger mb-3" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form id="formPhoneVerification" class="mb-3" action="{{ route('phone.verify.send') }}"
                            method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text">+62</span>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" 
                                        value="{{ old('phone', $user->no_hp ? preg_replace('/^62/', '', $user->no_hp) : '') }}"
                                        placeholder="812345678901" required autofocus pattern="[0-9]{9,13}"
                                        maxlength="13" />
                                </div>
                                <small class="text-muted">
                                    Masukkan nomor WhatsApp tanpa awalan 0 atau +62
                                    @if($user->no_hp && !$user->phone_verified_at)
                                        <span class="text-warning fw-bold">(Nomor saat ini belum terverifikasi)</span>
                                    @endif
                                </small>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Kirim Kode Verifikasi</button>
                            </div>
                        </form>

                        <p class="text-center">
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none p-0">
                                    <i class='bx bx-log-out'></i> Keluar dari akun
                                </button>
                            </form>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');

            // Load saved theme
            const savedTheme = localStorage.getItem('sifedora-theme') || 'light';
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
                updateThemeIcon('dark');
            }

            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                const currentTheme = body.classList.contains('dark-mode') ? 'dark' : 'light';
                localStorage.setItem('sifedora-theme', currentTheme);
                updateThemeIcon(currentTheme);
            });

            function updateThemeIcon(theme) {
                if (theme === 'dark') {
                    themeIcon.classList.remove('bx-moon');
                    themeIcon.classList.add('bx-sun');
                } else {
                    themeIcon.classList.remove('bx-sun');
                    themeIcon.classList.add('bx-moon');
                }
            }

            // Create animated background particles
            function createParticles() {
                const bgAnimation = document.getElementById('bgAnimation');
                const particleCount = 12;

                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');

                    const size = Math.random() * 60 + 30;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.top = `${Math.random() * 100}%`;
                    particle.style.animationDelay = `${Math.random() * 5}s`;
                    particle.style.animationDuration = `${Math.random() * 10 + 12}s`;

                    bgAnimation.appendChild(particle);
                }
            }

            createParticles();

            // Phone number formatting
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function(e) {
                // Remove non-digit characters
                let value = this.value.replace(/\D/g, '');

                // Remove leading zero if present
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }

                // Remove +62 if user accidentally pastes it
                if (value.startsWith('62')) {
                    value = value.substring(2);
                }

                this.value = value;
            });
        });
    </script>
</body>

</html>
