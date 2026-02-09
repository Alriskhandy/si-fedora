@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Undangan Pelaksanaan</h4>
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
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Cari Kabupaten/Kota..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status_undangan" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="belum_ada" {{ request('status_undangan') == 'belum_ada' ? 'selected' : '' }}>
                                    Belum Ada Undangan</option>
                                <option value="terkirim" {{ request('status_undangan') == 'terkirim' ? 'selected' : '' }}>
                                    Sudah Terkirim</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('undangan-pelaksanaan.index') }}" class="btn btn-outline-secondary">
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
                                <th>Jenis Dokumen</th>
                                <th>Kabupaten/Kota</th>
                                <th>Jadwal Fasilitasi</th>
                                <th>Status</th>
                                <th>Penerima</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($permohonan as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($permohonan->currentPage() - 1) * $permohonan->perPage() }}
                                    </td>
                                    <td>
                                        <strong>{{ $item->jenisDokumen->nama ?? '-' }}</strong><br>
                                    </td>
                                    <td>
                                        <strong>{{ $item->kabupatenKota->nama }}</strong><br>
                                    </td>
                                    <td>
                                        @if ($item->penetapanJadwal)
                                            <i class="bx bx-calendar text-primary"></i>
                                            {{ $item->penetapanJadwal->tanggal_mulai->format('d M Y') }}<br>
                                            <small class="text-muted">s/d {{ $item->penetapanJadwal->tanggal_selesai->format('d M Y') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->undanganPelaksanaan)
                                            <span class="badge bg-success">
                                                <i class="bx bx-check-circle"></i> Terkirim
                                            </span><br>
                                            <small class="text-muted">{{ $item->undanganPelaksanaan->tanggal_dikirim->format('d M Y') }}</small>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bx bx-x-circle"></i> Belum Ada
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->undanganPelaksanaan)
                                            <span class="badge bg-label-info">
                                                {{ $item->undanganPelaksanaan->jumlah_penerima }} orang
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->undanganPelaksanaan)
                                            <a href="{{ route('undangan-pelaksanaan.show', $item) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="bx bx-show"></i> Lihat
                                            </a>
                                        @else
                                            <a href="{{ route('undangan-pelaksanaan.create', $item) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Buat Undangan
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bx bx-info-circle bx-lg text-muted"></i>
                                        <p class="mb-0 mt-2">Tidak ada data permohonan dengan jadwal yang sudah ditetapkan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $permohonan->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
