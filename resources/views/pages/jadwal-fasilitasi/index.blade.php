@extends('layouts.app')

@section('title', 'Jadwal Fasilitasi')

@push('styles')
    <style>
        .table-responsive {
            overflow: visible !important;
        }

        .card-body {
            overflow: visible !important;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Jadwal Fasilitasi</h5>
                            @if (!auth()->user()->hasRole('admin_peran'))
                                <small class="text-muted">Jadwal fasilitasi yang tersedia untuk permohonan</small>
                            @endif
                        </div>
                        @if (auth()->user()->hasRole('admin_peran'))
                            <a href="{{ route('jadwal.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Tambah Jadwal
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <!-- Search & Filters -->
                        <form method="GET" action="{{ route('jadwal.index') }}">
                            <div class="row mb-3">
                                @if (auth()->user()->hasRole('admin_peran'))
                                    <div class="col-md-3">
                                        <select class="form-select" name="status">
                                            <option value="">Semua Status</option>
                                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                                                Draft
                                            </option>
                                            <option value="published"
                                                {{ request('status') == 'published' ? 'selected' : '' }}>
                                                Published</option>
                                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>
                                                Closed
                                            </option>
                                        </select>
                                    </div>
                                @endif
                                <div class="{{ auth()->user()->hasRole('admin_peran') ? 'col-md-3' : 'col-md-4' }}">
                                    <select class="form-select" name="tahun_anggaran">
                                        <option value="">Semua Tahun</option>
                                        @foreach ($filterOptions['tahunList'] as $tahun)
                                            <option value="{{ $tahun }}"
                                                {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                                {{ $tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="{{ auth()->user()->hasRole('admin_peran') ? 'col-md-3' : 'col-md-4' }}">
                                    <select class="form-select" name="jenis_dokumen">
                                        <option value="">Semua Jenis</option>
                                        @foreach ($filterOptions['jenisDokumenList'] as $jenisDokumen)
                                            <option value="{{ $jenisDokumen->id }}"
                                                {{ request('jenis_dokumen') == $jenisDokumen->id ? 'selected' : '' }}>
                                                {{ $jenisDokumen->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="{{ auth()->user()->hasRole('admin_peran') ? 'col-md-3' : 'col-md-4' }}">
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
                                        @if (auth()->user()->hasRole('admin_peran'))
                                            <th>Status</th>
                                        @endif
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
                                            @if (auth()->user()->hasRole('admin_peran'))
                                                <td>
                                                    @if ($item->status == 'draft')
                                                        <span class="badge bg-label-secondary">Draft</span>
                                                    @elseif($item->status == 'published')
                                                        <span class="badge bg-label-success">Published</span>
                                                    @else
                                                        <span class="badge bg-label-danger">Closed</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" data-bs-container="body"
                                                        data-bs-boundary="viewport">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('jadwal.show', $item) }}">
                                                            <i class="bx bx-show me-1"></i> Detail
                                                        </a>

                                                        @if ($item->undangan_file)
                                                            <a class="dropdown-item"
                                                                href="{{ route('jadwal.download', $item) }}">
                                                                <i class="bx bx-download me-1"></i> Unduh Dokumen
                                                            </a>
                                                        @endif

                                                        @if (auth()->user()->hasRole('pemohon') && $item->batas_permohonan >= now())
                                                            <a class="dropdown-item"
                                                                href="{{ route('permohonan.create', ['jadwal_id' => $item->id]) }}">
                                                                <i class="bx bx-plus-circle me-1"></i> Buat Permohonan
                                                            </a>
                                                        @endif

                                                        @if (auth()->user()->hasRole('admin_peran'))
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item"
                                                                href="{{ route('jadwal.edit', $item) }}">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <form action="{{ route('jadwal.destroy', $item) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Yakin ingin menghapus?')">
                                                                    <i class="bx bx-trash me-1"></i> Delete
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->hasRole('admin_peran') ? '7' : '6' }}"
                                                class="text-center text-muted">
                                                @if (auth()->user()->hasRole('admin_peran'))
                                                    Tidak ada jadwal fasilitasi
                                                @else
                                                    Belum ada jadwal fasilitasi yang tersedia
                                                @endif
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
