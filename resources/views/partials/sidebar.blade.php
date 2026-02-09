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

        <!-- ============================================= -->
        <!-- PROSES FASILITASI - SEMUA ROLE (kecuali superadmin) -->
        <!-- ============================================= -->
        @if (!auth()->user()->hasRole('superadmin'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">
                    Fasilitasi/Evaluasi
                </span>
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
                        <div data-i18n="Undangan">
                            @if (auth()->user()->hasRole('pemohon'))
                                Undangan Pelaksanaan
                            @else
                                Undangan Fasilitasi
                            @endif
                        </div>
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
                <span class="menu-header-text">Persetujuan & Penetapan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                <a href="{{ route('approval.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-circle"></i>
                    <div data-i18n="Approval">Approval Draft Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('penetapan-jadwal.*') ? 'active' : '' }}">
                <a href="{{ route('penetapan-jadwal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Penetapan Jadwal">Penetapan Jadwal Fasilitasi</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Monitoring & Laporan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                    <div data-i18n="Monitoring">Monitoring Progress</div>
                </a>
            </li>
        @endif

        {{-- ADMIN PERAN: Penjadwalan & Validasi --}}
        @if (auth()->user()->hasRole('admin_peran'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Validasi & Pelaksanaan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('validasi-hasil.*') ? 'active' : '' }}">
                <a href="{{ route('validasi-hasil.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-double"></i>
                    <div data-i18n="Validasi Hasil">Validasi Hasil Fasilitasi</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('undangan-pelaksanaan.*') ? 'active' : '' }}">
                <a href="{{ route('undangan-pelaksanaan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-mail-send"></i>
                    <div data-i18n="Undangan Pelaksanaan">Kelola Undangan</div>
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
                <span class="menu-header-text">Evaluasi Pokja</span>
            </li>
            <li class="menu-item {{ request()->routeIs('evaluasi.*') ? 'active' : '' }}">
                <a href="{{ route('evaluasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-analyse"></i>
                    <div data-i18n="Evaluasi">Evaluasi Dokumen</div>
                </a>
            </li>
        @endif

        {{-- AUDITOR: Activity Log --}}
        @if (auth()->user()->hasRole('auditor'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Sistem Audit</span>
            </li>
            <li class="menu-item {{ request()->routeIs('auditor.activity-log.*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Activity Log">Activity Log</div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- DOKUMEN & ADMINISTRASI - SEMUA ROLE -->
        <!-- ============================================= -->
        @if (!auth()->user()->hasRole('superadmin'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Dokumen & Administrasi</span>
            </li>

            {{-- Menu Dokumen Permohonan - Semua dokumen dari tahapan --}}
            <li class="menu-item {{ request()->routeIs('dokumen-permohonan.*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file-find"></i>
                    <div data-i18n="Dokumen Permohonan">
                        @if (auth()->user()->hasRole('pemohon'))
                            Dokumen Saya
                        @elseif(auth()->user()->hasRole('auditor'))
                            Arsip Dokumen
                        @else
                            Dokumen Permohonan
                        @endif
                    </div>
                </a>
            </li>

            {{-- Menu Laporan Verifikasi --}}
            @if (auth()->user()->hasAnyRole(['admin_peran', 'verifikator', 'auditor', 'kaban']))
                <li class="menu-item {{ request()->routeIs('laporan-verifikasi.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan-verifikasi.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-clipboard"></i>
                        <div data-i18n="Laporan Verifikasi">
                            @if (auth()->user()->hasRole('auditor'))
                                Data Verifikasi
                            @else
                                Laporan Verifikasi
                            @endif
                        </div>
                    </a>
                </li>
            @endif

            {{-- Menu Hasil Fasilitasi --}}
            @if (auth()->user()->hasAnyRole(['fasilitator', 'admin_peran', 'auditor', 'kaban']))
                <li class="menu-item {{ request()->routeIs('hasil-fasilitasi.*') ? 'active' : '' }}">
                    <a href="{{ route('hasil-fasilitasi.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-edit-alt"></i>
                        <div data-i18n="Hasil Fasilitasi">
                            @if (auth()->user()->hasRole('fasilitator'))
                                Input Hasil Fasilitasi
                            @elseif(auth()->user()->hasRole('auditor'))
                                Data Fasilitasi
                            @else
                                Hasil Fasilitasi
                            @endif
                        </div>
                    </a>
                </li>
            @endif

            {{-- Menu Surat & Administrasi --}}
            @if (auth()->user()->hasAnyRole(['admin_peran', 'kaban']))
                <li class="menu-item {{ request()->routeIs('surat-pemberitahuan.*') ? 'active' : '' }}">
                    <a href="{{ route('surat-pemberitahuan.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-envelope"></i>
                        <div data-i18n="Surat Pemberitahuan">Surat Pemberitahuan</div>
                    </a>
                </li>
            @endif

            {{-- Menu Surat Penyampaian Hasil - Semua bisa lihat --}}
            <li
                class="menu-item {{ request()->routeIs('public.surat-penyampaian-hasil*', 'surat-penyampaian-hasil.*') ? 'active' : '' }}">
                <a href="{{ auth()->user()->hasRole('kaban') ? route('surat-penyampaian-hasil.index') : route('public.surat-penyampaian-hasil') }}"
                    class="menu-link">
                    <i class="menu-icon tf-icons bx bx-paper-plane"></i>
                    <div data-i18n="Surat Penyampaian">Surat Penyampaian Hasil</div>
                </a>
            </li>

            {{-- Menu Surat Rekomendasi --}}
            @if (auth()->user()->hasAnyRole(['admin_peran', 'pemohon', 'auditor']))
                <li class="menu-item {{ request()->routeIs('surat-rekomendasi.*') ? 'active' : '' }}">
                    <a href="{{ route('surat-rekomendasi.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-notepad"></i>
                        <div data-i18n="Surat Rekomendasi">Surat Rekomendasi</div>
                    </a>
                </li>
            @endif

            {{-- Menu PERDA/PERKADA - Semua bisa lihat --}}
            <li
                class="menu-item {{ request()->routeIs('public.penetapan-perda', 'penetapan-perda.*', 'tindak-lanjut.*') ? 'active' : '' }}">
                <a href="{{ route('public.penetapan-perda') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                    <div data-i18n="Dokumen PERDA">
                        @if (auth()->user()->hasRole('pemohon'))
                            PERDA / Tindak Lanjut
                        @else
                            PERDA / PERKADA
                        @endif
                    </div>
                </a>
            </li>
        @endif

        <!-- ============================================= -->
        <!-- MANAJEMEN AKUN & TIM -->
        <!-- ============================================= -->
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
                    <div data-i18n="Kelengkapan Verifikasi">Dokumen Kelengkapan</div>
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
                <span class="menu-header-text">Pengaturan Sistem</span>
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
                <span class="menu-header-text">Monitoring & Audit</span>
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
