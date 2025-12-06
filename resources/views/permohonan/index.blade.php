@extends('layouts.app')

@section('title', 'Daftar Permohonan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Permohonan</h5>
                        @if (auth()->user()->hasRole('kabkota'))
                            <a href="{{ route('permohonan.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Buat Permohonan Baru
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <!-- Filter & Search -->
                        <form method="GET" action="{{ route('permohonan.index') }}">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Cari permohonan..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="tahun" class="form-select">
                                        <option value="">Semua Tahun</option>
                                        @foreach ($filterOptions['tahunList'] as $tahun)
                                            <option value="{{ $tahun }}"
                                                {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                                {{ $tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        @foreach ($filterOptions['statusOptions'] as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ request('status') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bx bx-search me-1"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permohonan as $index => $item)
                                        <tr>
                                            <td>{{ $index + $permohonan->firstItem() }}</td>
                                            <td>{{ $item->kabupatenKota?->nama ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-label-primary">
                                                    {{ strtoupper($item->jenis_dokumen) }}
                                                </span>
                                            </td>
                                            <td><strong>{{ $item->tahun }}</strong></td>
                                            <td>
                                                <span class="badge bg-label-{{ $item->status_badge_class }}">
                                                    {{ $item->status_label }}
                                                </span>
                                            </td>
                                            <td>{{ $item->created_at->format('d M Y') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('permohonan.show', $item) }}">
                                                            <i class="bx bx-show me-1"></i> Detail
                                                        </a>
                                                        @if ($item->status_akhir == 'belum' && auth()->user()->hasRole('pemohon'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('permohonan.edit', $item) }}">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <form action="{{ route('permohonan.destroy', $item) }}"
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
                                            <td colspan="7" class="text-center text-muted">Tidak ada data permohonan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $permohonan->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
