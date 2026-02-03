@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Validasi Hasil Fasilitasi</h4>
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

        <div class="card">
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Cari Kabupaten/Kota..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <select name="status_draft" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="diajukan" {{ request('status_draft') == 'diajukan' ? 'selected' : '' }}>
                                    Perlu Validasi</option>
                                <option value="disetujui" {{ request('status_draft') == 'disetujui' ? 'selected' : '' }}>
                                    Disetujui</option>
                                <option value="revisi" {{ request('status_draft') == 'revisi' ? 'selected' : '' }}>
                                    Revisi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('validasi-hasil.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-reset"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Nomor Permohonan</th>
                                <th>Fasilitator</th>
                                <th>Tanggal Diajukan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($hasilList as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($hasilList->currentPage() - 1) * $hasilList->perPage() }}
                                    </td>
                                    <td>
                                        <strong>{{ $item->permohonan->kabupatenKota->nama }}</strong>
                                    </td>
                                    <td>{{ $item->permohonan->no_permohonan }}</td>
                                    <td>{{ $item->pembuat->name }}</td>
                                    <td>
                                        {{ $item->tanggal_diajukan ? $item->tanggal_diajukan->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if ($item->status_draft == 'diajukan')
                                            <span class="badge bg-info">Perlu Validasi</span>
                                        @elseif($item->status_draft == 'revisi')
                                            <span class="badge bg-warning">Revisi</span>
                                        @else
                                            <span class="badge bg-success">Disetujui</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('validasi-hasil.show', $item->permohonan) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bx bx-show"></i> Lihat & Validasi
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data hasil fasilitasi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $hasilList->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
