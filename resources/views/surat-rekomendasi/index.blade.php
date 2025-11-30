@extends('layouts.app')

@section('title', 'Surat Rekomendasi')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Surat Rekomendasi</h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('surat-rekomendasi.index') }}">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari surat..." value="{{ request('search') }}">
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
                                    <th>Kabupaten/Kota</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Nomor Surat</th>
                                    <th>Tanggal Surat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permohonan as $index => $item)
                                <tr>
                                    <td>{{ $index + $permohonan->firstItem() }}</td>
                                    <td>{{ $item->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                    <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                    <td>{{ $item->nomor_surat ?? '-' }}</td>
                                    <td>{{ $item->tanggal_surat?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-success">Sudah Diterbitkan</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('surat-rekomendasi.show', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-show me-1"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada surat rekomendasi</td>
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