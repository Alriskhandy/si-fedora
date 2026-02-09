@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Undangan Saya</h4>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-9">
                            <select name="status_baca" class="form-select">
                                <option value="">Semua Undangan</option>
                                <option value="belum_dibaca"
                                    {{ request('status_baca') == 'belum_dibaca' ? 'selected' : '' }}>
                                    Belum Dibaca</option>
                                <option value="sudah_dibaca"
                                    {{ request('status_baca') == 'sudah_dibaca' ? 'selected' : '' }}>
                                    Sudah Dibaca</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ auth()->user()->hasRole('pemohon') ? route('pemohon.undangan.index') : route('my-undangan.index') }}"
                                class="btn btn-outline-secondary">
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
                                <th>Tanggal Fasilitasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($undanganList as $item)
                                <tr class="{{ !$item->dibaca ? 'table-active' : '' }}">
                                    <td>{{ $loop->iteration + ($undanganList->currentPage() - 1) * $undanganList->perPage() }}
                                    </td>
                                    <td>{{ $item->undangan->permohonan->kabupatenKota->nama }}</td>
                                    <td>
                                        {{ $item->undangan->penetapanJadwal->tanggal_mulai->format('d M Y') }} -
                                        {{ $item->undangan->penetapanJadwal->tanggal_selesai->format('d M Y') }}
                                    </td>
                                    <td>
                                        @if ($item->dibaca)
                                            <span class="badge bg-success">Sudah Dibaca</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Dibaca</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ auth()->user()->hasRole('pemohon') ? route('pemohon.undangan.view', $item->id) : route('my-undangan.view', $item->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bx bx-show"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada undangan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $undanganList->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
