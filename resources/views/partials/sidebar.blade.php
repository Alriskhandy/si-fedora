<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo my-3 d-flex justify-content-center">
        <a href="#" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/logo.webp') }}" alt="Logo SIFEDORA" width="80">
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- Dashboard --}}
        <li class="menu-item active">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        {{-- MENU KHUSUS SUPERADMIN & ADMIN PERAN --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'admin_peran'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Data Master</span>
        </li>

        {{-- Master Data --}}
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-data"></i>
                <div>Master Data</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Kabupaten/Kota</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Jenis Dokumen</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Persyaratan Dokumen</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Tahun Anggaran</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Tim Pokja</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Manajemen User --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>Manajemen User</div>
            </a>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK ADMIN PERAN --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'admin_peran'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Penjadwalan</span>
        </li>

        {{-- Jadwal Fasilitasi --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div>Jadwal Fasilitasi</div>
            </a>
        </li>

        {{-- Surat Pemberitahuan --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div>Surat Pemberitahuan</div>
            </a>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK KAB/KOTA --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'kabkota'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Permohonan</span>
        </li>

        {{-- Permohonan Fasilitasi --}}
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div>Permohonan Fasilitasi</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Daftar Permohonan</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Ajukan Permohonan</div>
                    </a>
                </li>
            </ul>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK TIM VERIFIKASI --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'admin_peran', 'tim_verifikasi'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Verifikasi</span>
        </li>

        {{-- Verifikasi Permohonan --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-check-circle"></i>
                <div>Verifikasi Dokumen</div>
            </a>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK TIM EVALUASI/POKJA --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'admin_peran', 'tim_evaluasi'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Evaluasi</span>
        </li>

        {{-- Penugasan Evaluasi --}}
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-edit"></i>
                <div>Evaluasi Dokumen</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Tugas Evaluasi</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div>Draft Rekomendasi</div>
                    </a>
                </li>
            </ul>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK ADMIN PERAN - Pengelolaan --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'admin_peran'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pengelolaan</span>
        </li>

        {{-- Kelola Permohonan --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-layer"></i>
                <div>Kelola Permohonan</div>
            </a>
        </li>

        {{-- Surat Rekomendasi --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-mail-send"></i>
                <div>Surat Rekomendasi</div>
            </a>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK KABAN --}}
        {{-- @if (in_array(auth()->user()->role ?? '', ['superadmin', 'kaban'])) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Persetujuan</span>
        </li>

        {{-- Approval Draft --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-clipboard"></i>
                <div>Persetujuan Draft</div>
            </a>
        </li>

        {{-- Approval Surat --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-pen"></i>
                <div>Tanda Tangan Surat</div>
            </a>
        </li>

        {{-- Laporan Executive --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                <div>Laporan Executive</div>
            </a>
        </li>
        {{-- @endif --}}

        {{-- MENU UNTUK SEMUA ROLE (LOGGED IN) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Lainnya</span>
        </li>

        {{-- Notifikasi --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div>Notifikasi</div>
            </a>
        </li>

        {{-- Profil --}}
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div>Profil Saya</div>
            </a>
        </li>

        {{-- Logout --}}
        <li class="menu-item">
            <form method="POST" action="#" id="logout-form">
                @csrf
                <a href="#" class="menu-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="menu-icon tf-icons bx bx-power-off"></i>
                    <div>Logout</div>
                </a>
            </form>
        </li>
    </ul>
</aside>
