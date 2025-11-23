@extends('layouts.app')

@section('title', 'Jadwal Fasilitasi')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Jadwal Fasilitasi</h5>
                    <a href="{{ route('jadwal.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Jadwal
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('jadwal.index') }}">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari jadwal..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bx bx-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Tahun</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Batas Permohonan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalFasilitasi as $index => $item)
                                <tr>
                                    <td>{{ $index + $jadwalFasilitasi->firstItem() }}</td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->tahunAnggaran->tahun ?? '-' }}</td>
                                    <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                    <td>{{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d M Y') : '-' }} - {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d M Y') : '-' }}</td>
<td>{{ $item->batas_permohonan ? $item->batas_permohonan->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($item->status == 'draft')
                                            <span class="badge bg-label-warning">Draft</span>
                                        @elseif($item->status == 'published')
                                            <span class="badge bg-label-success">Published</span>
                                        @else
                                            <span class="badge bg-label-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('jadwal.show', $item) }}">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="{{ route('jadwal.edit', $item) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                @if($item->status == 'draft')
                                                <form action="{{ route('jadwal.publish', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bx bx-send me-1"></i> Publish
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('jadwal.destroy', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" 
                                                            onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $jadwalFasilitasi->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection