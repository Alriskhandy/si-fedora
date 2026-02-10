@extends('layouts.app')

@section('title', 'Laporan Hasil Verifikasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Laporan Hasil Verifikasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan Verifikasi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('laporan-verifikasi.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cari Kabupaten/Kota</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama Kabupaten/Kota" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Laporan</label>
                        <select name="status_laporan" class="form-select">
                            <option value="">Semua</option>
                            <option value="belum_ada" {{ request('status_laporan') == 'belum_ada' ? 'selected' : '' }}>
                                Belum Dibuat
                            </option>
                            <option value="sudah_ada" {{ request('status_laporan') == 'sudah_ada' ? 'selected' : '' }}>
                                Sudah Dibuat
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class='bx bx-search'></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Permohonan -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Permohonan yang Sudah Diverifikasi</h5>
            </div>
            <div class="card-body">
                @if ($permohonan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tahun</th>
                                    <th>Status Verifikasi</th>
                                    <th>Status Laporan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permohonan as $index => $item)
                                    <tr>
                                        <td>{{ $permohonan->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $item->kabupatenKota->nama ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ strtoupper($item->jenisDokumen->nama) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->tahun }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class='bx bx-check-circle'></i> Selesai Diverifikasi
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->laporanVerifikasi)
                                                <span class="badge bg-success">
                                                    <i class='bx bx-check'></i> Sudah Dibuat
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $item->laporanVerifikasi->tanggal_laporan->format('d M Y') }}
                                                </small>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class='bx bx-time'></i> Belum Dibuat
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if ($item->laporanVerifikasi)
                                                    <a href="{{ route('laporan-verifikasi.show', $item) }}" 
                                                       class="btn btn-sm btn-info"
                                                       title="Lihat Laporan">
                                                        <i class='bx bx-show'></i>
                                                    </a>
                                                    <a href="{{ route('laporan-verifikasi.download', $item) }}" 
                                                       class="btn btn-sm btn-success"
                                                       title="Download PDF">
                                                        <i class='bx bx-download'></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('laporan-verifikasi.create', $item) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       title="Buat Laporan">
                                                        <i class='bx bx-plus'></i> Buat Laporan
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $permohonan->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class='bx bx-folder-open bx-lg text-muted mb-3 d-block'></i>
                        <p class="text-muted">Belum ada permohonan yang selesai diverifikasi</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
