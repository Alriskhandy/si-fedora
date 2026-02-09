@extends('layouts.app')

@section('title', 'Penetapan Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Penetapan Jadwal Fasilitasi / Evaluasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Penetapan Jadwal</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('penetapan-jadwal.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cari Kabupaten/Kota</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama Kabupaten/Kota" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Jadwal</label>
                        <select name="status_jadwal" class="form-select">
                            <option value="">Semua</option>
                            <option value="belum_ditetapkan" {{ request('status_jadwal') == 'belum_ditetapkan' ? 'selected' : '' }}>
                                Belum Ditetapkan
                            </option>
                            <option value="sudah_ditetapkan" {{ request('status_jadwal') == 'sudah_ditetapkan' ? 'selected' : '' }}>
                                Sudah Ditetapkan
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
                <h5 class="mb-0">Daftar Permohonan Siap Dijadwalkan</h5>
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
                                    <th>Status Laporan</th>
                                    <th>Status Jadwal</th>
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
                                                {{ strtoupper($item->jenisDokumen->nama ?? '-') }}
                                            </span>
                                        </td>
                                        <td>{{ $item->tahun }}</td>
                                        <td>
                                            @if($item->laporanVerifikasi)
                                                <span class="badge bg-success">
                                                    <i class='bx bx-check'></i> Lengkap
                                                </span>
                                            @else
                                                <span class="badge bg-warning">Belum Ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->penetapanJadwal)
                                                <span class="badge bg-success">
                                                    <i class='bx bx-calendar-check'></i> Sudah Ditetapkan
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class='bx bx-time'></i> Belum Ditetapkan
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if ($item->penetapanJadwal)
                                                    <a href="{{ route('penetapan-jadwal.show', $item) }}" 
                                                       class="btn btn-sm btn-info"
                                                       title="Lihat Jadwal">
                                                        <i class='bx bx-show'></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('penetapan-jadwal.create', $item) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       title="Tetapkan Jadwal">
                                                        <i class='bx bx-calendar-plus'></i> Tetapkan
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
                        <i class='bx bx-calendar-x bx-lg text-muted mb-3 d-block'></i>
                        <p class="text-muted">Belum ada permohonan yang siap dijadwalkan</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
