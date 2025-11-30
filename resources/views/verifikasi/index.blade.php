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
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>
                                    Menunggu Verifikasi
                                </option>
                                <option value="revision_required" {{ request('status') == 'revision_required' ? 'selected' : '' }}>
                                    Perlu Revisi
                                </option>
                            </select>
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
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permohonan as $index => $item)
                                <tr>
                                    <td>{{ $index + $permohonan->firstItem() }}</td>
                                    <td>{{ $item->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                    <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                    </td>
                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('verifikasi.show', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-show me-1"></i> Verifikasi
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada permohonan</td>
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