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

        <!-- PUBLIC MENU (All Authenticated Users) -->
        <li class="menu-item {{ request()->routeIs('public.surat-penyampaian-hasil*') ? 'active' : '' }}">
            <a href="{{ route('public.surat-penyampaian-hasil') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file-find"></i>
                <div data-i18n="Surat Penyampaian">Surat Penyampaian Hasil</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('public.penetapan-perda') ? 'active' : '' }}">
            <a href="{{ route('public.penetapan-perda') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder-open"></i>
                <div data-i18n="Dokumen PERDA">PERDA / PERKADA</div>
            </a>
        </li>

        <!-- KABAN -->
        @if (auth()->user()->hasRole('kaban'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Persetujuan & Penetapan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                <a href="{{ route('approval.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div data-i18n="Approval">Approval Draft Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('penetapan-jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('penetapan-jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Penetapan Jadwal">Penetapan Jadwal</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('surat-penyampaian-hasil.*') ? 'active' : '' }}">
                <a href="{{ route('surat-penyampaian-hasil.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-find"></i>
                    <div data-i18n="Surat Penyampaian">Surat Penyampaian Hasil</div>
                </a>
            </li>
            {{-- <li class="menu-item {{ request()->routeIs('penetapan-perda.*') ? 'active' : '' }}">
                <a href="{{ route('penetapan-perda.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-blank"></i>
                    <div data-i18n="Penetapan PERDA">Penetapan PERDA/PERKADA</div>
                </a>
            </li> --}}
            <li class="menu-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                <a href="{{ route('monitoring.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                    <div data-i18n="Monitoring">Monitoring Progress</div>
                </a>
            </li>
        @endif

        <!-- ADMIN PERAN -->
        @if (auth()->user()->hasRole('admin_peran'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Penjadwalan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                    <div data-i18n="Jadwal">Jadwal Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('surat-pemberitahuan.*') ? 'active' : '' }}">
                <a href="{{ route('surat-pemberitahuan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope"></i>
                    <div data-i18n="Surat Pemberitahuan">Surat Pemberitahuan</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Penugasan & Validasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('laporan-verifikasi.*') ? 'active' : '' }}">
                <a href="{{ route('laporan-verifikasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-find"></i>
                    <div data-i18n="Laporan Verifikasi">Laporan Verifikasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('undangan-pelaksanaan.*') ? 'active' : '' }}">
                <a href="{{ route('undangan-pelaksanaan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                    <div data-i18n="Undangan Pelaksanaan">Undangan Pelaksanaan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('validasi-hasil.*') ? 'active' : '' }}">
                <a href="{{ route('validasi-hasil.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-double"></i>
                    <div data-i18n="Validasi Hasil">Validasi Hasil Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('surat-rekomendasi.*') ? 'active' : '' }}">
                <a href="{{ route('surat-rekomendasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-paper-plane"></i>
                    <div data-i18n="Surat Rekomendasi">Surat Rekomendasi</div>
                </a>
            </li>
        @endif

        <!-- PEMOHON -->
        @if (auth()->user()->hasRole('pemohon'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Permohonan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('pemohon.jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('pemohon.jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                    <div data-i18n="Jadwal">Jadwal Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*', 'permohonan-dokumen.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Permohonan">Permohonan Saya</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('my-undangan.*') ? 'active' : '' }}">
                <a href="{{ route('my-undangan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                    <div data-i18n="Undangan">Undangan Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('tindak-lanjut.*') ? 'active' : '' }}">
                <a href="{{ route('tindak-lanjut.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div data-i18n="Tindak Lanjut">Tindak Lanjut</div>
                </a>
            </li>
        @endif

        <!-- VERIFIKATOR -->
        @if (auth()->user()->hasRole('verifikator'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Verifikasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('verifikasi.*') ? 'active' : '' }}">
                <a href="{{ route('verifikasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-circle"></i>
                    <div data-i18n="Verifikasi">Verifikasi Dokumen</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('my-undangan.*') ? 'active' : '' }}">
                <a href="{{ route('my-undangan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope"></i>
                    <div data-i18n="Undangan">Undangan Fasilitasi</div>
                </a>
            </li>
        @endif

        <!-- FASILITATOR -->
        @if (auth()->user()->hasRole('fasilitator'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Fasilitator</span>
            </li>
            <li class="menu-item {{ request()->routeIs('my-undangan.*') ? 'active' : '' }}">
                <a href="{{ route('my-undangan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope"></i>
                    <div data-i18n="Undangan">Undangan Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('hasil-fasilitasi.*') ? 'active' : '' }}">
                <a href="{{ route('hasil-fasilitasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-edit"></i>
                    <div data-i18n="Hasil Fasilitasi">Input Hasil Fasilitasi</div>
                </a>
            </li>
        @endif

        <!-- POKJA -->
        @if (auth()->user()->hasRole('pokja'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Pokja</span>
            </li>
            <li class="menu-item {{ request()->routeIs('evaluasi.*') ? 'active' : '' }}">
                <a href="{{ route('evaluasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-analyse"></i>
                    <div data-i18n="Evaluasi">Evaluasi</div>
                </a>
            </li>
        @endif

        <!-- MASTER DATA -->
        @if (auth()->user()->hasAnyRole(['superadmin', 'admin_peran']))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Akun & Tim</span>
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

        <!-- MASTER DATA -->
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
            <li class="menu-item {{ request()->routeIs('master-tahapan.*') ? 'active' : '' }}">
                <a href="{{ route('master-tahapan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ol"></i>
                    <div data-i18n="Master Tahapan">Tahapan Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-jenis-dokumen.*') ? 'active' : '' }}">
                <a href="{{ route('master-jenis-dokumen.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Jenis Dokumen">Jenis Dokren</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-bab.*') ? 'active' : '' }}">
                <a href="{{ route('master-bab.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div data-i18n="Master Bab">Sistematika</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-urusan.*') ? 'active' : '' }}">
                <a href="{{ route('master-urusan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-briefcase"></i>
                    <div data-i18n="Master Urusan">Urusan Pemerintahan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-kelengkapan.*') ? 'active' : '' }}">
                <a href="{{ route('master-kelengkapan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-square"></i>
                    <div data-i18n="Kelengkapan Verifikasi">Dokumen Kelengkapan</div>
                </a>
            </li>
        @endif

        <!-- SISTEM (Superadmin Only) -->
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
        @endif

        <!-- LOGOUT -->
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
