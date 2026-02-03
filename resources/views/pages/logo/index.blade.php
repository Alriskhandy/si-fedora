<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filosofi Logo SI-FEDORA</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background: linear-gradient(135deg, #2345DEFF 0%, #162799FF 100%);
            color: #333;
            overflow-x: hidden;
            padding: 2rem 1rem;
        }

        .bg-particles {
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
            background: rgba(255, 255, 255, 0.3);
            animation: float 20s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-30px) translateX(20px); }
            50% { transform: translateY(-60px) translateX(-20px); }
            75% { transform: translateY(-30px) translateX(20px); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        header {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            background: white;
            width: 200px;
            height: 200px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: pulse 3s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4); }
        }

        .logo-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes shine {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo-svg {
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 5px 15px rgba(105, 108, 255, 0.3));
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
        }

        .subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
        }

        .philosophy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .philosophy-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: fadeInUp 0.8s ease-out backwards;
            position: relative;
            overflow: hidden;
        }

        .philosophy-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .philosophy-card:hover::before {
            transform: scaleX(1);
        }

        .philosophy-card:nth-child(1) { animation-delay: 0.1s; }
        .philosophy-card:nth-child(2) { animation-delay: 0.2s; }
        .philosophy-card:nth-child(3) { animation-delay: 0.3s; }
        .philosophy-card:nth-child(4) { animation-delay: 0.4s; }
        .philosophy-card:nth-child(5) { animation-delay: 0.5s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .philosophy-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1F44EBFF 0%, #4B71A2FF 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .philosophy-card:hover .icon-wrapper {
            transform: rotate(360deg) scale(1.1);
        }

        .icon-wrapper i {
            font-size: 2rem;
            color: white;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .card-description {
            font-size: 1rem;
            color: #666;
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .card-meaning {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #555;
            border-left: 4px solid #667eea;
        }

        .color-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out 0.6s backwards;
        }

        .color-section h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .color-section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #2041D5FF, #1133AEFF);
            border-radius: 2px;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .color-card {
            text-align: center;
            transition: transform 0.3s ease;
        }

        .color-card:hover {
            transform: translateY(-5px);
        }

        .color-box {
            width: 100%;
            height: 150px;
            border-radius: 15px;
            margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .color-card:hover .color-box {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }

        .color-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: rotate(45deg);
            transition: all 0.5s ease;
        }

        .color-card:hover .color-box::before {
            left: 100%;
        }

        .color-blue {
            background: linear-gradient(135deg, #667eea 0%, #4c63d2 100%);
        }

        .color-cyan {
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
        }

        .color-magenta {
            background: linear-gradient(135deg, #ff00ff 0%, #cc00cc 100%);
        }

        .color-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .color-meaning {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
        }

        .message-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            color: white;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease-out 0.8s backwards;
            position: relative;
            overflow: hidden;
        }

        .message-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .message-section h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .message-section p {
            font-size: 1.2rem;
            line-height: 1.8;
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 0.3rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .badge:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        footer {
            text-align: center;
            margin-top: 4rem;
            color: white;
            font-size: 0.95rem;
            animation: fadeIn 1s ease-out 1s backwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .philosophy-grid {
                grid-template-columns: 1fr;
            }

            .color-grid {
                grid-template-columns: 1fr;
            }

            .philosophy-card,
            .color-section,
            .message-section {
                padding: 2rem 1.5rem;
            }

            .logo-container {
                width: 150px;
                height: 150px;
            }

            .logo-svg {
                width: 90px;
                height: 90px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-particles" id="bgParticles"></div>

    <div class="container">
        <header>
            <div class="logo-container">
                {{-- <svg class="logo-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"> --}}
                  <img src="{{ asset('assets/img/icons/logo.png') }}" alt="Logo SIFEDORA" width="150" class="logo-svg">
                    <!-- S Shape with gradient -->
                    <defs>
                        <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#00d4ff;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#ff00ff;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <!-- S Path -->
                    <path d="M 30 25 Q 50 15 70 25 Q 80 35 70 45 Q 50 55 30 45 Q 50 55 70 65 Q 80 75 70 85 Q 50 95 30 85" 
                          stroke="url(#logoGradient)" stroke-width="8" fill="none" stroke-linecap="round"/>
                    <!-- Check mark -->
                    <path d="M 35 50 L 45 60 L 65 35" 
                          stroke="url(#logoGradient)" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Human dot -->
                    <circle cx="50" cy="75" r="5" fill="url(#logoGradient)"/>
                </svg>
            </div>
            <h1>Filosofi Logo SI-FEDORA</h1>
            <p class="subtitle">Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan</p>
        </header>

        <div class="philosophy-grid">
            <div class="philosophy-card">
                <div class="icon-wrapper">
                    <i class='bx bx-font'></i>
                </div>
                <h3 class="card-title">Bentuk Huruf "S"</h3>
                <p class="card-description">
                    Melambangkan nama sistem SI-FEDORA dengan desain yang elegan dan modern.
                </p>
                <div class="card-meaning">
                    <strong>Makna:</strong> Mencerminkan alur yang fleksibel & terintegrasi, menggambarkan proses fasilitasi dan evaluasi dokumen perencanaan yang efisien dan terstruktur.
                </div>
            </div>

            <div class="philosophy-card">
                <div class="icon-wrapper">
                    <i class='bx bx-check-circle'></i>
                </div>
                <h3 class="card-title">Simbol Check / Centang</h3>
                <p class="card-description">
                    Menggambarkan validasi, persetujuan, dan kualitas dokumen yang terjamin.
                </p>
                <div class="card-meaning">
                    <strong>Makna:</strong> Menandakan bahwa sistem ini membantu memastikan dokumen perencanaan tepat, layak, dan akuntabel.
                </div>
            </div>

            <div class="philosophy-card">
                <div class="icon-wrapper">
                    <i class='bx bx-user-circle'></i>
                </div>
                <h3 class="card-title">Titik / Lingkaran Manusia</h3>
                <p class="card-description">
                    Melambangkan kolaborasi, partisipasi publik, dan peran pemerintah daerah.
                </p>
                <div class="card-meaning">
                    <strong>Makna:</strong> Menunjukkan bahwa sistem ini berorientasi pada pelayanan dan pengguna (human-centered service).
                </div>
            </div>

            <div class="philosophy-card">
                <div class="icon-wrapper">
                    <i class='bx bx-cuboid'></i>
                </div>
                <h3 class="card-title">Style Gradient 3D</h3>
                <p class="card-description">
                    Desain modern dengan efek tiga dimensi yang menawan dan futuristik.
                </p>
                <div class="card-meaning">
                    <strong>Makna:</strong> Mewakili transformasi digital dan percepatan layanan berbasis sistem. Memberikan kesan dinamis & visioner.
                </div>
            </div>
        </div>

        <div class="color-section">
            <h2>🎨 Filosofi Warna</h2>
            <div class="color-grid">
                <div class="color-card">
                    <div class="color-box color-blue"></div>
                    <h3 class="color-name">Blue (Biru)</h3>
                    <p class="color-meaning">
                        Profesional, terpercaya, stabilitas pemerintahan
                    </p>
                </div>

                <div class="color-card">
                    <div class="color-box color-cyan"></div>
                    <h3 class="color-name">Cyan (Biru Muda)</h3>
                    <p class="color-meaning">
                        Inovasi, teknologi, keterbukaan data
                    </p>
                </div>

                <div class="color-card">
                    <div class="color-box color-magenta"></div>
                    <h3 class="color-name">Magenta (Ungu Merah)</h3>
                    <p class="color-meaning">
                        Kreativitas, energi perubahan, semangat membangun daerah
                    </p>
                </div>
            </div>
            <p style="text-align: center; margin-top: 2rem; color: #666; font-size: 1.05rem;">
                Kombinasi warna & shadow memberi kesan <strong>modern, futuristik, dan digital government</strong>.
            </p>
        </div>

        <div class="message-section">
            <h2>📍 Pesan Keseluruhan</h2>
            <p>
                Logo SI-FEDORA menggambarkan semangat <strong>transformasi digital perencanaan daerah</strong>, 
                mengutamakan <span class="badge">Kolaborasi</span> <span class="badge">Transparansi</span> 
                <span class="badge">Akurasi</span> <span class="badge">Kualitas Dokumen</span> 
                untuk pembangunan Kabupaten/Kota yang lebih baik.
            </p>
        </div>

        <footer>
            <p>&copy; 2025 SI-FEDORA - Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan</p>
        </footer>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('bgParticles');
            const particleCount = 20;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 50 + 20;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
                particle.style.animationDuration = `${Math.random() * 10 + 15}s`;
                
                container.appendChild(particle);
            }
        }

        createParticles();

        // Smooth scroll reveal
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.philosophy-card, .color-section, .message-section').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>