@extends('layouts.app')

@section('title', 'Management Tim Assignment')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Master Data /</span> Tim Assignment Kabupaten/Kota
            </h4>
            <a href="{{ route('tim-assignment.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah Assignment
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('tim-assignment.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kabupaten/Kota</label>
                        <select name="kabupaten_kota_id" class="form-select">
                            <option value="">Semua Kabupaten/Kota</option>
                            @foreach ($allKabkota as $kabkota)
                                <option value="{{ $kabkota->id }}"
                                    {{ request('kabupaten_kota_id') == $kabkota->id ? 'selected' : '' }}>
                                    {{ $kabkota->getFullNameAttribute() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Role</label>
                        <select name="role_type" class="form-select">
                            <option value="">Semua Role</option>
                            <option value="koordinator" {{ request('role_type') == 'koordinator' ? 'selected' : '' }}>
                                Koordinator
                            </option>
                            <option value="verifikator" {{ request('role_type') == 'verifikator' ? 'selected' : '' }}>
                                Verifikator
                            </option>
                            <option value="fasilitator" {{ request('role_type') == 'fasilitator' ? 'selected' : '' }}>
                                Fasilitator
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ request('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('tim-assignment.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tim Assignment Cards -->
        @forelse ($kabkotaList as $kabkota)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="bx bx-map-pin text-primary me-2"></i>
                            {{ $kabkota->getFullNameAttribute() }}
                        </h5>
                        <small class="text-muted">
                            <i class="bx bx-group"></i>
                            {{ $kabkota->userAssignments->count() }} Tim Member
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $koordinators = $kabkota->userAssignments->where('role_type', 'koordinator');
                        $verifikators = $kabkota->userAssignments->where('role_type', 'verifikator');
                        $fasilitators = $kabkota->userAssignments->where('role_type', 'fasilitator');
                    @endphp

                    <div class="row">
                        <!-- Koordinator -->
                        @if ($koordinators->isNotEmpty())
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-shield-alt-2"></i> Koordinator
                                    </h6>
                                    @foreach ($koordinators as $assignment)
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <strong>{{ $assignment->user->name }}</strong>
                                                    @if ($assignment->is_pic)
                                                        <span class="badge bg-warning ms-2">
                                                            <i class="bx bx-star"></i> PIC
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted d-block">
                                                    <i class="bx bx-envelope"></i> {{ $assignment->user->email }}
                                                </small>
                                                @if ($assignment->assigned_from || $assignment->assigned_until)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="bx bx-calendar"></i>
                                                        {{ $assignment->assigned_from ? $assignment->assigned_from->format('d/m/Y') : '-' }}
                                                        s/d
                                                        {{ $assignment->assigned_until ? $assignment->assigned_until->format('d/m/Y') : '∞' }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-icon"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item"
                                                        href="{{ route('tim-assignment.edit', $assignment) }}">
                                                        <i class="bx bx-edit me-1"></i> Edit
                                                    </a>
                                                    <form action="{{ route('tim-assignment.destroy', $assignment) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menonaktifkan?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bx bx-x me-1"></i> Nonaktifkan
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Verifikator (PIC & Anggota) -->
                        @if ($verifikators->isNotEmpty())
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-info mb-3">
                                        <i class="bx bx-check-shield"></i> Verifikator
                                    </h6>

                                    @php
                                        $picVerifikator = $verifikators->where('is_pic', true)->first();
                                        $anggotaVerifikator = $verifikators->where('is_pic', false);
                                    @endphp

                                    @if ($picVerifikator)
                                        <div class="mb-3 pb-2 border-bottom">
                                            <small class="text-muted d-block mb-2"><strong>PIC / Ketua Tim:</strong></small>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <strong>{{ $picVerifikator->user->name }}</strong>
                                                        <span class="badge bg-warning ms-2">
                                                            <i class="bx bx-star"></i> PIC
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        <i class="bx bx-envelope"></i> {{ $picVerifikator->user->email }}
                                                    </small>
                                                    @if ($picVerifikator->assigned_from || $picVerifikator->assigned_until)
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="bx bx-calendar"></i>
                                                            {{ $picVerifikator->assigned_from ? $picVerifikator->assigned_from->format('d/m/Y') : '-' }}
                                                            s/d
                                                            {{ $picVerifikator->assigned_until ? $picVerifikator->assigned_until->format('d/m/Y') : '∞' }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-icon"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="{{ route('tim-assignment.edit', $picVerifikator) }}">
                                                            <i class="bx bx-edit me-1"></i> Edit
                                                        </a>
                                                        <form
                                                            action="{{ route('tim-assignment.destroy', $picVerifikator) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin menonaktifkan?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bx bx-x me-1"></i> Nonaktifkan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($anggotaVerifikator->isNotEmpty())
                                        <small class="text-muted d-block mb-2"><strong>Anggota:</strong></small>
                                        @foreach ($anggotaVerifikator as $assignment)
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <strong class="d-block">{{ $assignment->user->name }}</strong>
                                                    <small class="text-muted d-block">
                                                        <i class="bx bx-envelope"></i> {{ $assignment->user->email }}
                                                    </small>
                                                    @if ($assignment->assigned_from || $assignment->assigned_until)
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="bx bx-calendar"></i>
                                                            {{ $assignment->assigned_from ? $assignment->assigned_from->format('d/m/Y') : '-' }}
                                                            s/d
                                                            {{ $assignment->assigned_until ? $assignment->assigned_until->format('d/m/Y') : '∞' }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-icon"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="{{ route('tim-assignment.edit', $assignment) }}">
                                                            <i class="bx bx-edit me-1"></i> Edit
                                                        </a>
                                                        <form action="{{ route('tim-assignment.destroy', $assignment) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin menonaktifkan?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bx bx-x me-1"></i> Nonaktifkan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Fasilitator (PIC & Anggota) -->
                        @if ($fasilitators->isNotEmpty())
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-success mb-3">
                                        <i class="bx bx-user-check"></i> Fasilitator
                                    </h6>

                                    @php
                                        $picFasilitator = $fasilitators->where('is_pic', true)->first();
                                        $anggotaFasilitator = $fasilitators->where('is_pic', false);
                                    @endphp

                                    @if ($picFasilitator)
                                        <div class="mb-3 pb-2 border-bottom">
                                            <small class="text-muted d-block mb-2"><strong>PIC / Ketua
                                                    Tim:</strong></small>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <strong>{{ $picFasilitator->user->name }}</strong>
                                                        <span class="badge bg-warning ms-2">
                                                            <i class="bx bx-star"></i> PIC
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        <i class="bx bx-envelope"></i> {{ $picFasilitator->user->email }}
                                                    </small>
                                                    @if ($picFasilitator->assigned_from || $picFasilitator->assigned_until)
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="bx bx-calendar"></i>
                                                            {{ $picFasilitator->assigned_from ? $picFasilitator->assigned_from->format('d/m/Y') : '-' }}
                                                            s/d
                                                            {{ $picFasilitator->assigned_until ? $picFasilitator->assigned_until->format('d/m/Y') : '∞' }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-icon"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="{{ route('tim-assignment.edit', $picFasilitator) }}">
                                                            <i class="bx bx-edit me-1"></i> Edit
                                                        </a>
                                                        <form
                                                            action="{{ route('tim-assignment.destroy', $picFasilitator) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin menonaktifkan?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bx bx-x me-1"></i> Nonaktifkan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($anggotaFasilitator->isNotEmpty())
                                        <small class="text-muted d-block mb-2"><strong>Anggota:</strong></small>
                                        @foreach ($anggotaFasilitator as $assignment)
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <strong class="d-block">{{ $assignment->user->name }}</strong>
                                                    <small class="text-muted d-block">
                                                        <i class="bx bx-envelope"></i> {{ $assignment->user->email }}
                                                    </small>
                                                    @if ($assignment->assigned_from || $assignment->assigned_until)
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="bx bx-calendar"></i>
                                                            {{ $assignment->assigned_from ? $assignment->assigned_from->format('d/m/Y') : '-' }}
                                                            s/d
                                                            {{ $assignment->assigned_until ? $assignment->assigned_until->format('d/m/Y') : '∞' }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-icon"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="{{ route('tim-assignment.edit', $assignment) }}">
                                                            <i class="bx bx-edit me-1"></i> Edit
                                                        </a>
                                                        <form action="{{ route('tim-assignment.destroy', $assignment) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin menonaktifkan?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bx bx-x me-1"></i> Nonaktifkan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-folder-open" style="font-size: 48px; color: #ddd;"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada tim assignment</p>
                    <small class="text-muted">Tambahkan assignment tim untuk kabupaten/kota</small>
                </div>
            </div>
        @endforelse
    </div>
@endsection
