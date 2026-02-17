@extends('layouts.app')

@section('title', 'Arsip Dokumen')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Arsip Dokumen</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Arsip Dokumen</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Alert Section -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body" style="background-color: #f8f9fa;">
                <form method="GET" action="{{ route('arsip.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Cari</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama Kab/Kota, Dokumen..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Jenis Dokumen</label>
                        <select name="jenis_dokumen_id" class="form-select">
                            <option value="">Semua Jenis</option>
                            @foreach($jenisDokumenList as $jenis)
                                <option value="{{ $jenis->id }}" 
                                    {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                    {{ $jenis->nama_dokumen }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Tahun</label>
                        <select name="tahun" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach($tahunList as $tahun)
                                <option value="{{ $tahun }}" 
                                    {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status_akhir" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="proses" {{ request('status_akhir') == 'proses' ? 'selected' : '' }}>Proses</option>
                            <option value="selesai" {{ request('status_akhir') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ request('status_akhir') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-success me-2">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Daftar Permohonan</h5>
                    <p class="text-muted small mb-0">Klik pada baris untuk melihat detail dokumen</p>
                </div>
                <span class="badge bg-label-primary">{{ $permohonan->total() }} Permohonan</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Jenis Dokumen</th>
                                <th>Tahun</th>
                                <th>Pemohon</th>
                                <th>Tahapan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permohonan as $index => $item)
                                <tr style="cursor: pointer;" onclick="window.location='{{ route('arsip.show', $item) }}'">
                                    <td>{{ $permohonan->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $item->kabupatenKota->nama ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $item->jenisDokumen->nama_dokumen ?? 'N/A' }}</td>
                                    <td>{{ $item->tahun }}</td>
                                    <td>{{ $item->pemohon->name ?? '-' }}</td>
                                    <td>
                                        @if($item->tahapanAktif && $item->tahapanAktif->masterTahapan)
                                            <span class="badge bg-label-info">
                                                {{ $item->tahapanAktif->masterTahapan->nama_tahapan }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">Belum Dimulai</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status_akhir == 'selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($item->status_akhir == 'ditolak')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @else
                                            <span class="badge bg-warning">Proses</span>
                                        @endif
                                    </td>
                                    <td onclick="event.stopPropagation();">
                                        <a href="{{ route('arsip.show', $item) }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="bx bx-folder-open me-1"></i> Lihat Dokumen
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bx bx-folder-open" style="font-size: 48px;"></i>
                                        <p class="mb-0 mt-2">Tidak ada data permohonan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($permohonan->hasPages())
                    <div class="mt-4">
                        {{ $permohonan->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
