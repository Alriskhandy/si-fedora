<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/logo.webp') }}" alt="Logo SIFEDORA" width="40">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">SIFEDORA</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- DOKUMEN & ADMINISTRASI - SEMUA ROLE -->
        <li
            class="menu-item {{ request()->routeIs('arsip.*') ? 'active' : '' }}">
            <a href="{{ route('arsip.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-archive"></i>
                <div data-i18n="Arsip Dokumen">Arsip Dokumen</div>
            </a>
        </li>

        <!-- ============================================= -->
        <!-- PROSES FASILITASI - SEMUA ROLE (kecuali superadmin) -->
        <!-- ============================================= -->
        @if (!auth()->user()->hasRole('superadmin'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Proses Fasilitasi</span>
            </li>

            <li class="menu-item {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                    <div data-i18n="Jadwal">Jadwal Pelaksanaan</div>
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('permohonan.*', 'permohonan-dokumen.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Permohonan">
                        @if (auth()->user()->hasRole('pemohon'))
                            Permohonan Saya
                        @else
                            Daftar Permohonan
                        @endif
                    </div>
                </a>
            </li>

            @if (auth()->user()->hasAnyRole(['pemohon', 'verifikator', 'fasilitator']))
                <li
                    class="menu-item {{ request()->routeIs('my-undangan.*', 'undangan-pelaksanaan.*') ? 'active' : '' }}">
                    <a href="{{ route('my-undangan.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                        <div data-i18n="Undangan">Undangan Saya</div>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasAnyRole(['fasilitator', 'verifikator']))
                <li class="menu-item {{ request()->routeIs('hasil-fasilitasi.*') ? 'active' : '' }}">
                    <a href="{{ route('hasil-fasilitasi.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-edit"></i>
                        <div data-i18n="Input Hasil">Input Hasil</div>
                    </a>
                </li>
            @endif
        @endif

        <!-- ============================================= -->
        <!-- FUNGSI KHUSUS PER ROLE -->
        <!-- ============================================= -->

        {{-- KABAN: Approval & Penetapan --}}
        @if (auth()->user()->hasRole('kaban'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Persetujuan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                <a href="{{ route('approval.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-circle"></i>
                    <div data-i18n="Approval">Hasil Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('penetapan-jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('penetapan-jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Penetapan Jadwal">Penetapan Jadwal</div>
                </a>
            </li>
        @endif

        {{-- ADMIN PERAN: Penjadwalan & Validasi --}}
        @if (auth()->user()->hasRole('admin_peran'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Validasi & Koordinasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('undangan-pelaksanaan.*') ? 'active' : '' }}">
                <a href="{{ route('undangan-pelaksanaan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-mail-send"></i>
                    <div data-i18n="Undangan Pelaksanaan">Undangan Pelaksanaan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('perpanjangan-waktu.*') ? 'active' : '' }}">
                <a href="{{ route('perpanjangan-waktu.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-time-five"></i>
                    <div data-i18n="Perpanjangan Waktu">Perpanjangan Waktu</div>
                </a>
            </li>
        @endif

        {{-- POKJA: Evaluasi Dokumen --}}
        @if (auth()->user()->hasRole('pokja'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Evaluasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('evaluasi.*') ? 'active' : '' }}">
                <a href="{{ route('evaluasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-analyse"></i>
                    <div data-i18n="Evaluasi">Evaluasi Dokumen</div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- MANAJEMEN AKUN & TIM -->
        <!-- ============================================= -->
        @if (auth()->user()->hasAnyRole(['superadmin', 'admin_peran']))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen</span>
            </li>
            <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Users">Akun Pengguna</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('tim-assignment.*') ? 'active' : '' }}">
                <a href="{{ route('tim-assignment.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div data-i18n="Tim Assignment">Tim FEDORA</div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- MASTER DATA -->
        <!-- ============================================= -->
        @if (auth()->user()->hasAnyRole(['superadmin', 'admin_peran']))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>
            <li class="menu-item {{ request()->routeIs('kabupaten-kota.*') ? 'active' : '' }}">
                <a href="{{ route('kabupaten-kota.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-buildings"></i>
                    <div data-i18n="Kabupaten/Kota">Kabupaten/Kota</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-jenis-dokumen.*') ? 'active' : '' }}">
                <a href="{{ route('master-jenis-dokumen.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Jenis Dokumen">Jenis Dokumen</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-tahapan.*') ? 'active' : '' }}">
                <a href="{{ route('master-tahapan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ol"></i>
                    <div data-i18n="Master Tahapan">Tahapan Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-kelengkapan.*') ? 'active' : '' }}">
                <a href="{{ route('master-kelengkapan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-square"></i>
                    <div data-i18n="Kelengkapan Verifikasi">Kelengkapan Dokumen</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-bab.*') ? 'active' : '' }}">
                <a href="{{ route('master-bab.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div data-i18n="Master Bab">Sistematika Dokumen</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-urusan.*') ? 'active' : '' }}">
                <a href="{{ route('master-urusan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-briefcase"></i>
                    <div data-i18n="Master Urusan">Urusan Pemerintahan</div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- SISTEM (Superadmin Only) -->
        <!-- ============================================= -->
        @if (auth()->user()->hasRole('superadmin'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Sistem</span>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <a href="{{ route('admin.roles.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-shield"></i>
                    <div data-i18n="Roles">Manajemen Role</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-key"></i>
                    <div data-i18n="Permissions">Manajemen Permission</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
                <a href="{{ route('notifikasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bell"></i>
                    <div data-i18n="Notifikasi">Notifikasi Sistem</div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- MONITORING & AUDIT -->
        <!-- ============================================= -->
        @if (auth()->user()->hasAnyRole(['superadmin', 'admin_peran', 'kaban', 'auditor']))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Monitoring</span>
            </li>
            <li class="menu-item {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
                <a href="{{ route('activity-log.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Activity Log">Log Aktivitas</div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- LOGOUT -->
        <!-- ============================================= -->
        <li class="menu-item">
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <a href="{{ route('logout') }}" class="menu-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="menu-icon tf-icons bx bx-power-off"></i>
                    <div>Logout</div>
                </a>
            </form>
        </li>
    </ul>
</aside>
