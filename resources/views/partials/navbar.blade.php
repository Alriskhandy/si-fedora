<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        {{-- Search --}}
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input type="text" class="form-control border-0 shadow-none" placeholder="Cari..."
                    aria-label="Cari" />
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            {{-- Modul Pengguna --}}
            <li class="nav-item me-2">
                <a class="btn btn-sm btn-outline-primary" href="{{ asset('modul-pengguna/MODUL PENGGUNAAN SI FEDORA.pdf') }}" 
                   target="_blank" title="Download Modul Pengguna">
                    <i class="bx bx-book-open me-1"></i>
                    <span class="d-none d-md-inline">Modul Penggunaan</span>
                </a>
            </li>

            {{-- Notifications --}}
            <li class="nav-item navbar-dropdown dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="{{ route('notifikasi.index') }}" >
                    <i class="bx bx-bell bx-sm"></i>
                    <span class="badge bg-danger rounded-pill badge-notifications">3</span>
                </a>
            </li>

            {{-- User Profile --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                      
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                  
                                    <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                                
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">Profil Saya</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Pengaturan</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form-navbar">
                            @csrf
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form-navbar').submit();">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Logout</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>