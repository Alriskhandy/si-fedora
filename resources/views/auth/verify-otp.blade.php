<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="Sistem Informasi Fedora - Verifikasi OTP" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi OTP - SIFEDORA</title>

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

        body.dark-mode .btn-outline-secondary {
            border-color: #3d4a64;
            color: #b4bdc6;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #2d3a54;
            border-color: #4dabf7;
            color: #4dabf7;
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

        /* Elements Fade In */
        .otp-icon {
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

        /* OTP Input Styling */
        .otp-input-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .otp-input {
            width: 50px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #d9dee3;
            border-radius: 8px;
            transition: all 0.2s ease;
            caret-color: transparent;
        }

        .otp-input:focus {
            border-color: #696cff;
            outline: none;
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1);
        }

        .otp-input.filled {
            border-color: #696cff;
            background-color: rgba(105, 108, 255, 0.05);
            animation: fillPulse 0.3s ease;
        }

        @keyframes fillPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.08);
            }
        }

        body.dark-mode .otp-input {
            background-color: #2d3a54;
            border-color: #3d4a64;
            color: #ffffff;
        }

        body.dark-mode .otp-input:focus {
            border-color: #4dabf7;
            box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.1);
        }

        body.dark-mode .otp-input.filled {
            border-color: #4dabf7;
            background-color: rgba(77, 171, 247, 0.1);
        }

       /* OTP Icon */
        .otp-icon {
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

        body.dark-mode .otp-icon {
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

        /* Timer */
        .timer {
            font-size: 18px;
            font-weight: 600;
            color: #e70400;
        }

        body.dark-mode .timer {
            color: #4dabf7;
        }

        .timer.expired {
            color: #ea868f;
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

        /* Verification Message */
        .verification-message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .verification-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .verification-message i {
            font-size: 24px;
            flex-shrink: 0;
        }

        .verification-message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .verification-message.success i {
            color: #28a745;
        }

        .verification-message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .verification-message.error i {
            color: #dc3545;
        }

        body.dark-mode .verification-message.success {
            background-color: rgba(40, 167, 69, 0.15);
            border-color: rgba(40, 167, 69, 0.3);
            color: #69db7c;
        }

        body.dark-mode .verification-message.success i {
            color: #51cf66;
        }

        body.dark-mode .verification-message.error {
            background-color: rgba(220, 53, 69, 0.15);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ffa8a8;
        }

        body.dark-mode .verification-message.error i {
            color: #ff6b6b;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }

        .loading-overlay.show {
            display: flex;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: #4dabf7;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            font-size: 18px;
            font-weight: 500;
            margin-top: 15px;
        }

        .loading-subtext {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 8px;
        }

        /* Button Loading State */
        .btn-primary.loading {
            pointer-events: none;
            position: relative;
            color: transparent !important;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .theme-toggle {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }

            .otp-input {
                width: 45px;
                height: 50px;
                font-size: 20px;
            }

            .loading-spinner {
                width: 50px;
                height: 50px;
            }

            .loading-text {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Memverifikasi...</div>
            <div class="loading-subtext">Mohon tunggu sebentar</div>
        </div>
    </div>

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
                        <div class="otp-icon">
                            <i class='bx bxl-whatsapp'></i>
                        </div>

                        <h5 class="mb-2 text-center">Verifikasi Kode OTP</h5>
                        <p class="mb-4 text-center">Masukkan 6 digit kode yang telah dikirim ke WhatsApp<br>
                            <strong>{{ session('phone_masked', '+62 ***') }}</strong>
                        </p>

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

                        <form id="formOtpVerification" class="mb-3" action="{{ route('phone.verify.otp') }}"
                            method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label text-center d-block">Kode OTP</label>
                                <div class="otp-input-container">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]"
                                        inputmode="numeric" data-index="0" />
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]"
                                        inputmode="numeric" data-index="1" />
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]"
                                        inputmode="numeric" data-index="2" />
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]"
                                        inputmode="numeric" data-index="3" />
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]"
                                        inputmode="numeric" data-index="4" />
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]"
                                        inputmode="numeric" data-index="5" />
                                </div>
                                <input type="hidden" name="otp" id="otpValue" required />
                                @error('otp')
                                    <div class="invalid-feedback d-block text-center">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 text-center">
                                <p class="mb-2">
                                    <span class="timer" id="timer">05:00</span>
                                </p>
                                <p>
                                    <small>Tidak menerima kode?
                                        <a href="#" id="resendLink" class="text-decoration-none">
                                            Kirim ulang
                                        </a>
                                    </small>
                                </p>
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit" id="verifyBtn">Verifikasi</button>
                            </div>
                        </form>

                        <p class="text-center">
                            <a href="{{ route('phone.verify') }}" class="text-decoration-none">
                                <i class='bx bx-chevron-left'></i> Ubah nomor WhatsApp
                            </a>
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

            // OTP Input Logic
            const otpInputs = document.querySelectorAll('.otp-input');
            const otpValue = document.getElementById('otpValue');
            const form = document.getElementById('formOtpVerification');
            const verifyBtn = document.getElementById('verifyBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');

            // Form Submit Handler with AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const otp = otpValue.value;
                
                if (otp.length !== 6) {
                    showMessage('error', 'Kode OTP harus 6 digit');
                    return;
                }
                
                // Show loading state
                verifyBtn.classList.add('loading');
                verifyBtn.disabled = true;
                loadingOverlay.classList.add('show');
                
                // Disable OTP inputs
                otpInputs.forEach(input => input.disabled = true);
                
                // Send AJAX request
                fetch('{{ route('phone.verify.otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ otp: otp })
                })
                .then(response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new TypeError('Server tidak mengembalikan JSON response');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage('success', data.message || 'Verifikasi berhasil!');
                        
                        // Redirect after 1.5 seconds
                        setTimeout(() => {
                            window.location.href = data.redirect || '{{ route('dashboard') }}';
                        }, 1500);
                    } else {
                        // Show error message
                        showMessage('error', data.message || 'Verifikasi gagal. Silakan coba lagi.');
                        
                        // Re-enable form
                        verifyBtn.classList.remove('loading');
                        verifyBtn.disabled = false;
                        loadingOverlay.classList.remove('show');
                        otpInputs.forEach(input => input.disabled = false);
                        
                        // Clear OTP inputs
                        otpInputs.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                        });
                        otpValue.value = '';
                        otpInputs[0].focus();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'Terjadi kesalahan. Silakan coba lagi.');
                    
                    // Re-enable form
                    verifyBtn.classList.remove('loading');
                    verifyBtn.disabled = false;
                    loadingOverlay.classList.remove('show');
                    otpInputs.forEach(input => input.disabled = false);
                });
            });
            
            // Function to show message
            function showMessage(type, message) {
                // Remove existing message
                const existingMessage = document.querySelector('.verification-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                // Create message element
                const messageDiv = document.createElement('div');
                messageDiv.className = `verification-message ${type}`;
                messageDiv.innerHTML = `
                    <i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'}"></i>
                    <span>${message}</span>
                `;
                
                // Insert before form
                form.parentElement.insertBefore(messageDiv, form);
                
                // Trigger animation
                setTimeout(() => {
                    messageDiv.classList.add('show');
                }, 10);
                
                // Auto remove error messages after 5 seconds
                if (type === 'error') {
                    setTimeout(() => {
                        messageDiv.classList.remove('show');
                        setTimeout(() => messageDiv.remove(), 300);
                    }, 5000);
                }
            }

            otpInputs.forEach((input, index) => {
                // Input event - handle character entry
                input.addEventListener('input', function(e) {
                    const value = this.value;

                    // Only allow single digit
                    if (value.length > 1) {
                        this.value = value.charAt(value.length - 1);
                    }

                    // Only allow digits
                    if (!/^\d*$/.test(this.value)) {
                        this.value = '';
                        return;
                    }

                    // Add filled class for animation
                    if (this.value) {
                        this.classList.add('filled');
                    } else {
                        this.classList.remove('filled');
                    }

                    // Auto move to next input
                    if (this.value && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                        otpInputs[index + 1].select();
                    }

                    // Update hidden input
                    updateOtpValue();
                });

                // Keydown event - handle navigation and deletion
                input.addEventListener('keydown', function(e) {
                    // Backspace handling
                    if (e.key === 'Backspace') {
                        e.preventDefault();
                        
                        if (this.value) {
                            // Clear current input
                            this.value = '';
                            this.classList.remove('filled');
                            updateOtpValue();
                        } else if (index > 0) {
                            // Move to previous and clear
                            otpInputs[index - 1].focus();
                            otpInputs[index - 1].value = '';
                            otpInputs[index - 1].classList.remove('filled');
                            updateOtpValue();
                        }
                        return;
                    }

                    // Arrow right
                    if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                        e.preventDefault();
                        otpInputs[index + 1].focus();
                        otpInputs[index + 1].select();
                        return;
                    }

                    // Arrow left
                    if (e.key === 'ArrowLeft' && index > 0) {
                        e.preventDefault();
                        otpInputs[index - 1].focus();
                        otpInputs[index - 1].select();
                        return;
                    }

                    // Prevent non-numeric keys (except navigation)
                    if (!/^\d$/.test(e.key) && 
                        !['Backspace', 'ArrowLeft', 'ArrowRight', 'Tab', 'Delete'].includes(e.key)) {
                        e.preventDefault();
                    }
                });

                // Focus event - select all for easy replacement
                input.addEventListener('focus', function() {
                    this.select();
                });

                // Paste event - distribute digits across inputs
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text');
                    const digits = pastedData.match(/\d/g);

                    if (digits) {
                        digits.forEach((digit, i) => {
                            const targetIndex = index + i;
                            if (targetIndex < otpInputs.length) {
                                otpInputs[targetIndex].value = digit;
                                otpInputs[targetIndex].classList.add('filled');
                            }
                        });
                        updateOtpValue();

                        // Focus last filled input or next empty
                        const lastFilledIndex = Math.min(index + digits.length - 1, otpInputs.length - 1);
                        const nextEmptyIndex = lastFilledIndex + 1;
                        
                        if (nextEmptyIndex < otpInputs.length) {
                            otpInputs[nextEmptyIndex].focus();
                        } else {
                            otpInputs[lastFilledIndex].focus();
                        }
                    }
                });
            });

            // Auto-focus first input on page load
            if (otpInputs.length > 0) {
                setTimeout(() => {
                    otpInputs[0].focus();
                    otpInputs[0].select();
                }, 100);
            }

            function updateOtpValue() {
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                otpValue.value = otp;
            }

            // Timer Countdown (5 minutes)
            let timeLeft = 300; // 5 minutes in seconds
            const timerElement = document.getElementById('timer');
            const resendLink = document.getElementById('resendLink');

            // Make global for resend function
            window.timeLeft = timeLeft;

            function updateTimer() {
                const minutes = Math.floor(window.timeLeft / 60);
                const seconds = window.timeLeft % 60;
                timerElement.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (window.timeLeft <= 0) {
                    timerElement.classList.add('expired');
                    timerElement.textContent = 'Kode expired';
                    resendLink.style.pointerEvents = 'auto';
                    resendLink.style.opacity = '1';
                } else {
                    window.timeLeft--;
                    setTimeout(updateTimer, 1000);
                }
            }

            // Make updateTimer global for resend function
            window.updateTimer = updateTimer;

            updateTimer();

            // Focus first input
            otpInputs[0].focus();
            
            // Attach resend link event listener
            resendLink.addEventListener('click', function(event) {
                resendOtp(event);
            });
        });

        function resendOtp(event) {
            event.preventDefault();

            const resendLink = document.getElementById('resendLink');
            resendLink.style.pointerEvents = 'none';
            resendLink.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Mengirim...';

            // Send AJAX request to resend OTP
            fetch('{{ route('phone.verify.resend') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success mb-3';
                        alertDiv.setAttribute('role', 'alert');
                        alertDiv.textContent = data.message || 'Kode OTP baru telah dikirim ke WhatsApp Anda';
                        
                        const cardBody = document.querySelector('.card-body');
                        const existingAlert = cardBody.querySelector('.alert');
                        if (existingAlert) {
                            existingAlert.remove();
                        }
                        cardBody.insertBefore(alertDiv, cardBody.querySelector('form'));
                        
                        // Reset OTP inputs
                        document.querySelectorAll('.otp-input').forEach(input => input.value = '');
                        document.getElementById('otpValue').value = '';
                        document.querySelector('.otp-input').focus();
                        
                        // Reset timer
                        timeLeft = 300;
                        const timerElement = document.getElementById('timer');
                        timerElement.classList.remove('expired');
                        updateTimer();
                        
                        resendLink.innerHTML = 'Kirim ulang';
                        resendLink.style.pointerEvents = 'auto';
                    } else {
                        alert(data.message || 'Gagal mengirim ulang kode OTP');
                        resendLink.innerHTML = 'Kirim ulang';
                        resendLink.style.pointerEvents = 'auto';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    resendLink.innerHTML = 'Kirim ulang';
                    resendLink.style.pointerEvents = 'auto';
                });
        }
    </script>
</body>

</html>
