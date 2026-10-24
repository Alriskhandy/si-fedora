@extends('layouts.app')

@section('title', 'Arsip - ' . $jenisDokumen->nama)

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    {{ $jenisDokumen->nama }}
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('arsip.index') }}">Arsip Dokumen</a></li>
                        <li class="breadcrumb-item active">{{ $jenisDokumen->nama }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body" style="background-color: #f8f9fa;">
                <form method="GET" action="{{ route('arsip.listByJenis', $jenisDokumen->id) }}" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Cari Kabupaten/Kota atau Tahun</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Masukkan nama Kabupaten/Kota atau Tahun..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tahun</label>
                        <select name="tahun" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('arsip.listByJenis', $jenisDokumen->id) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="card-title mb-1">Daftar Permohonan</h5>
                    <p class="text-muted small mb-0">Klik pada baris untuk melihat detail lengkap dokumen</p>
                </div>
                <span class="badge bg-primary rounded-pill fs-6 px-3 py-2">
                    <i class='bx bx-file me-1'></i>
                    {{ $permohonan->total() }} Dokumen
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Tahun</th>
                                <th>Tanggal Selesai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permohonan as $index => $item)
                                <tr class="clickable-row" style="cursor: pointer;"
                                    onclick="window.location='{{ route('arsip.show', $item) }}'">
                                    <td class="px-4">{{ $permohonan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class='bx bx-map text-primary'></i>
                                            </div>
                                            <div>
                                                <strong
                                                    class="text-dark">{{ $item->kabupatenKota->nama ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $item->tahun }}</span>
                                    </td>
                                    
                                    <td>
                                        <small class="text-muted">
                                            <i class='bx bx-calendar me-1'></i>
                                            {{ $item->updated_at ? $item->updated_at->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <a href="{{ route('arsip.show', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-folder-open me-1"></i> Buka
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bx bx-folder-open text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mb-0 mt-2">Belum ada dokumen untuk jenis ini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($permohonan->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $permohonan->firstItem() }} sampai {{ $permohonan->lastItem() }}
                            dari {{ $permohonan->total() }} dokumen
                        </div>
                        <div>
                            {{ $permohonan->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Hover effect for table rows */
        .clickable-row {
            transition: all 0.2s ease;
        }

        .clickable-row:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        /* Avatar styling */
        .avatar-sm {
            width: 36px;
            height: 36px;
        }

        /* Card hover improvements */
        .card {
            transition: all 0.3s ease;
        }

        /* Badge styling improvements */
        .badge {
            font-weight: 500;
            padding: 0.35rem 0.65rem;
        }
    </style>
@endsection
