@extends('layouts.app')

@section('title', 'Verifikasi Permohonan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Verifikasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search & Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('verifikasi.index') }}">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search"
                                            placeholder="Cari permohonan..." value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bx bx-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <form method="GET" action="{{ route('verifikasi.index') }}" id="filterForm">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <select name="status" class="form-select"
                                        onchange="document.getElementById('filterForm').submit()">
                                        <option value="">Semua Status</option>
                                        <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>
                                            Menunggu Verifikasi
                                        </option>
                                        <option value="revisi" {{ request('status') == 'revisi' ? 'selected' : '' }}>
                                            Perlu Revisi
                                        </option>
                                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>
                                            Terverifikasi
                                        </option>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th>Tanggal Submit</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permohonan as $index => $item)
                                        <tr>
                                            <td>{{ $index + $permohonan->firstItem() }}</td>
                                            <td>{{ $item->kabupatenKota->nama ?? '-' }}</td>
                                            <td><span class="badge bg-primary">{{ strtoupper($item->jenis_dokumen) }}</span>
                                            </td>
                                            <td>{{ $item->tahun }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-label-{{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                            </td>
                                            <td>{{ $item->submitted_at ? $item->submitted_at->format('d M Y H:i') : '-' }}
                                            </td>
                                            <td>
                                                <a href="{{ route('verifikasi.show', $item) }}"
                                                    class="btn btn-sm btn-{{ $item->status_akhir == 'selesai' ? 'success' : 'primary' }}">
                                                    <i
                                                        class="bx bx-{{ $item->status_akhir == 'selesai' ? 'check-circle' : 'show' }} me-1"></i>
                                                    {{ $item->status_akhir == 'selesai' ? 'Lihat' : 'Verifikasi' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Tidak ada permohonan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $permohonan->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
