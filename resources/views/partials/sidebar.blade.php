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
        {{-- Dashboard (Semua Role) --}}
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        {{-- ========================================
            1. SUPERADMIN MENU
        ========================================= --}}
        @if (auth()->user()->hasRole('superadmin'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen User</span>
            </li>
            <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Users">Akun Pengguna</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.roles.*', 'admin.permissions.*') ? 'active' : '' }}">
                <a href="{{ route('admin.roles.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-shield"></i>
                    <div data-i18n="Roles">Role & Permission</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>
            <li class="menu-item {{ request()->routeIs('kabupaten-kota.*') ? 'active' : '' }}">
                <a href="{{ route('kabupaten-kota.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-buildings"></i>
                    <div data-i18n="Kabupaten/Kota">Kabupaten/Kota</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('master-urusan.*') ? 'active' : '' }}">
                <a href="{{ route('master-urusan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-briefcase"></i>
                    <div data-i18n="Master Urusan">Urusan Pemerintahan</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->routeIs('master-jenis-dokumen.*', 'master-bab.*', 'master-tahapan.*', 'master-kelengkapan.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div data-i18n="Master Lainnya">Master Lainnya</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->routeIs('master-jenis-dokumen.*') ? 'active' : '' }}">
                        <a href="{{ route('master-jenis-dokumen.index') }}" class="menu-link">
                            <div data-i18n="Jenis Dokumen">Jenis Dokumen</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-bab.*') ? 'active' : '' }}">
                        <a href="{{ route('master-bab.index') }}" class="menu-link">
                            <div data-i18n="Sistematika">Sistematika</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-tahapan.*') ? 'active' : '' }}">
                        <a href="{{ route('master-tahapan.index') }}" class="menu-link">
                            <div data-i18n="Tahapan">Tahapan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-kelengkapan.*') ? 'active' : '' }}">
                        <a href="{{ route('master-kelengkapan.index') }}" class="menu-link">
                            <div data-i18n="Kelengkapan">Kelengkapan</div>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Fasilitasi / Evaluasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Permohonan">Semua Fasilitasi</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Laporan & Sistem</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-find"></i>
                    <div data-i18n="Laporan">Laporan Rekap</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Audit Log">Audit Log</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div data-i18n="Pengaturan">Pengaturan Sistem</div>
                </a>
            </li>
        @endif

        {{-- ========================================
            2. KABAN MENU
        ========================================= --}}
        @if (auth()->user()->hasRole('kaban'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Fasilitasi / Evaluasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Fasilitasi">Fasilitasi / Evaluasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                <a href="{{ route('approval.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div data-i18n="Approval">Approval & Persetujuan</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('penetapan-jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('penetapan-jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Penetapan Jadwal">Penetapan Jadwal</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Dokumen Final</span>
            </li>
            <li class="menu-item {{ request()->routeIs('surat-penyampaian-hasil.*') ? 'active' : '' }}">
                <a href="{{ route('surat-penyampaian-hasil.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-find"></i>
                    <div data-i18n="Surat Penyampaian">Surat Hasil Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('public.penetapan-perda') ? 'active' : '' }}">
                <a href="{{ route('public.penetapan-perda') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Dokumen PERDA">PERDA / PERKADA</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Laporan</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                    <div data-i18n="Laporan">Rekap Hasil Fasilitasi</div>
                </a>
            </li>
        @endif

        {{-- ========================================
            3. ADMIN PERAN MENU
        ========================================= --}}
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
                <span class="menu-header-text">Fasilitasi / Evaluasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Fasilitasi">Semua Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('tim-assignment.*') ? 'active' : '' }}">
                <a href="{{ route('tim-assignment.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div data-i18n="Tim Assignment">Tim FEDORA</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Proses Fasilitasi</span>
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
                    <div data-i18n="Validasi Hasil">Validasi Hasil</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Dokumen & Notifikasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('perpanjangan-waktu.*') ? 'active' : '' }}">
                <a href="{{ route('perpanjangan-waktu.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-time-five"></i>
                    <div data-i18n="Perpanjangan Waktu">Perpanjangan Waktu</div>
                </a>
            </li>
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

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data & Pengguna</span>
            </li>
            <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Users">Akun Pengguna</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->routeIs('kabupaten-kota.*', 'master-urusan.*', 'master-jenis-dokumen.*', 'master-bab.*', 'master-tahapan.*', 'master-kelengkapan.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-data"></i>
                    <div data-i18n="Master Data">Master Data</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->routeIs('kabupaten-kota.*') ? 'active' : '' }}">
                        <a href="{{ route('kabupaten-kota.index') }}" class="menu-link">
                            <div data-i18n="Kabupaten/Kota">Kab/Kota</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-urusan.*') ? 'active' : '' }}">
                        <a href="{{ route('master-urusan.index') }}" class="menu-link">
                            <div data-i18n="Master Urusan">Urusan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-jenis-dokumen.*') ? 'active' : '' }}">
                        <a href="{{ route('master-jenis-dokumen.index') }}" class="menu-link">
                            <div data-i18n="Jenis Dokumen">Jenis Dokumen</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-bab.*') ? 'active' : '' }}">
                        <a href="{{ route('master-bab.index') }}" class="menu-link">
                            <div data-i18n="Sistematika">Sistematika</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-tahapan.*') ? 'active' : '' }}">
                        <a href="{{ route('master-tahapan.index') }}" class="menu-link">
                            <div data-i18n="Tahapan">Tahapan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('master-kelengkapan.*') ? 'active' : '' }}">
                        <a href="{{ route('master-kelengkapan.index') }}" class="menu-link">
                            <div data-i18n="Kelengkapan">Kelengkapan</div>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Laporan</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                    <div data-i18n="Laporan">Laporan Rekap</div>
                </a>
            </li>
        @endif

        {{-- ========================================
            4. VERIFIKATOR MENU
        ========================================= --}}
        @if (auth()->user()->hasRole('verifikator'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Verifikasi Dokumen</span>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*', 'verifikasi.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Verifikasi">Fasilitasi / Evaluasi</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Notifikasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('my-undangan.*') ? 'active' : '' }}">
                <a href="{{ route('my-undangan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope"></i>
                    <div data-i18n="Undangan">Undangan Fasilitasi</div>
                </a>
            </li>
        @endif

        {{-- ========================================
            5. FASILITATOR MENU
        ========================================= --}}
        @if (auth()->user()->hasRole('fasilitator'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Fasilitasi Berjalan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('my-undangan.*') ? 'active' : '' }}">
                <a href="{{ route('my-undangan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                    <div data-i18n="Undangan">Jadwal Fasilitasi</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Hasil Fasilitasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('hasil-fasilitasi.*') ? 'active' : '' }}">
                <a href="{{ route('hasil-fasilitasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-edit"></i>
                    <div data-i18n="Hasil Fasilitasi">Input Hasil Fasilitasi</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Notifikasi</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bell"></i>
                    <div data-i18n="Notifikasi">Notifikasi</div>
                </a>
            </li>
        @endif

        {{-- ========================================
            6. PEMOHON (KAB/KOTA) MENU
        ========================================= --}}
        @if (auth()->user()->hasRole('pemohon'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Pengajuan Saya</span>
            </li>
            <li class="menu-item {{ request()->routeIs('pemohon.jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('pemohon.jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                    <div data-i18n="Jadwal">Jadwal Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Permohonan">Permohonan Saya</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Dokumen</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-upload"></i>
                    <div data-i18n="Upload">Upload Dokumen</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Tindak Lanjut</span>
            </li>
            <li class="menu-item {{ request()->routeIs('tindak-lanjut.*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div data-i18n="Tindak Lanjut">Laporan Tindak Lanjut</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Notifikasi</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bell"></i>
                    <div data-i18n="Notifikasi">Notifikasi</div>
                </a>
            </li>
        @endif

        {{-- ========================================
            7. AUDITOR MENU
        ========================================= --}}
        @if (auth()->user()->hasRole('auditor'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Data Fasilitasi</span>
            </li>
            <li class="menu-item {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
                <a href="{{ route('permohonan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Fasilitasi">Semua Fasilitasi</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Dokumen</span>
            </li>
            <li class="menu-item {{ request()->routeIs('public.surat-penyampaian-hasil*') ? 'active' : '' }}">
                <a href="{{ route('public.surat-penyampaian-hasil') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-find"></i>
                    <div data-i18n="Surat Penyampaian">Surat Hasil Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('public.penetapan-perda') ? 'active' : '' }}">
                <a href="{{ route('public.penetapan-perda') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div data-i18n="Dokumen PERDA">PERDA / PERKADA</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Audit & Laporan</span>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Audit Log">Audit Log</div>
                </a>
            </li>
        @endif

        {{-- LOGOUT (Semua Role) --}}
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
