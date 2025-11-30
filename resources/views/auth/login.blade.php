


<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="Sistem Informasi Fedora - Halaman Login" />

    <title>Login - SIFEDORA</title>

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
            background-color: #f5f5f9;
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
            background-color: #0f1729;
            border-color: #2d3a54;
            color: #fff;
        }

        body.dark-mode .form-control:focus {
            border-color: #4dabf7;
            box-shadow: 0 0 0 0.2rem rgba(77, 171, 247, 0.25);
        }

        body.dark-mode .form-control::placeholder {
            color: #6c7a89;
        }

        body.dark-mode .input-group-text {
            background-color: #0f1729;
            border-color: #2d3a54;
            color: #b4bdc6;
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

        body.dark-mode .form-check-input {
            background-color: #0f1729;
            border-color: #2d3a54;
        }

        body.dark-mode .form-check-input:checked {
            background-color: #4dabf7;
            border-color: #4dabf7;
        }

        body.dark-mode .invalid-feedback {
            color: #ea868f;
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
            0%, 100% {
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
            transition: transform 0.2s ease;
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
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/logo/index') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset('assets/img/icons/logo.png') }}" alt="Logo SIFEDORA" width="150">
                                    {{-- <img src="{{ asset('assets/img/logo.webp') }}" alt="Logo SIFEDORA" width="150"> --}}
                                </span>
                            </a>
                        </div>

                        <h5 class="mb-2 text-center">Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota</h5>
                        <p class="mb-4 text-center">Silakan masuk ke akun Anda</p>

                        @if (session('status'))
                            <div class="alert alert-success mb-3" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="Masukkan email Anda" required autofocus autocomplete="username" />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Kata Sandi</label>
                                    @if (app('router')->getRoutes()->hasNamedRoute('password.request'))
                                        <a href="{{ route('password.request') }}">
                                            <small>Lupa Kata Sandi?</small>
                                        </a>
                                    @endif
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        required autocomplete="current-password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember" />
                                    <label class="form-check-label" for="remember_me"> Ingat Saya </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                            </div>
                        </form>
                        
                        <p class="text-center">
                            <small>Belum punya akun? Hubungi administrator untuk pendaftaran.</small>
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
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.querySelector('.form-password-toggle .input-group-text');
            const passwordInput = document.getElementById('password');
            
            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('bx-hide');
                    this.querySelector('i').classList.toggle('bx-show');
                });
            }

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
        });
    </script>
</body>

</html>