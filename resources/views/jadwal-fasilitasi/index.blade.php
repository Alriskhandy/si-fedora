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
                        <!-- Search & Filters -->
                        <form method="GET" action="{{ route('jadwal.index') }}">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Cari tahun atau dokumen..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                        </option>
                                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                                            Published</option>
                                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="jenis_dokumen">
                                        <option value="">Semua Jenis</option>
                                        <option value="rkpd" {{ request('jenis_dokumen') == 'rkpd' ? 'selected' : '' }}>
                                            RKPD</option>
                                        <option value="rpd" {{ request('jenis_dokumen') == 'rpd' ? 'selected' : '' }}>RPD
                                        </option>
                                        <option value="rpjmd" {{ request('jenis_dokumen') == 'rpjmd' ? 'selected' : '' }}>
                                            RPJMD</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-secondary w-100" type="submit">
                                        <i class="bx bx-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tahun Anggaran</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Tanggal Fasilitasi</th>
                                        <th>Batas Permohonan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwalFasilitasi as $index => $item)
                                        <tr>
                                            <td>{{ $index + $jadwalFasilitasi->firstItem() }}</td>
                                            <td><strong>{{ $item->tahun_anggaran }}</strong></td>
                                            <td>
                                                <span class="badge bg-label-primary">
                                                    {{ strtoupper($item->jenisDokumen->nama) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d M Y') : '-' }}
                                                <small class="text-muted">s/d</small>
                                                {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d M Y') : '-' }}
                                            </td>
                                            <td>
                                                @if ($item->batas_permohonan)
                                                    <span
                                                        class="badge bg-label-{{ $item->batas_permohonan < now() ? 'danger' : 'success' }}">
                                                        {{ $item->batas_permohonan->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->status == 'draft')
                                                    <span class="badge bg-label-secondary">Draft</span>
                                                @elseif($item->status == 'published')
                                                    <span class="badge bg-label-success">Published</span>
                                                @else
                                                    <span class="badge bg-label-danger">Closed</span>
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
                                                        <form action="{{ route('jadwal.destroy', $item) }}" method="POST"
                                                            class="d-inline">
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
                                            <td colspan="7" class="text-center text-muted">Tidak ada jadwal fasilitasi
                                            </td>
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
