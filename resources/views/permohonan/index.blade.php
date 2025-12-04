@extends('layouts.app')

@section('title', 'Daftar Permohonan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Permohonan</h5>
                    @if(auth()->user()->hasRole('kabkota'))
                    <a href="{{ route('permohonan.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Buat Permohonan Baru
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Filter & Search -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <form method="GET" action="{{ route('permohonan.index') }}">
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
                            <select name="tahun_anggaran_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Tahun</option>
                                @foreach($filterOptions['tahunAnggaran'] as $tahun)
                                    <option value="{{ $tahun->id }}" 
                                            {{ request('tahun_anggaran_id') == $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                @foreach($filterOptions['statusOptions'] as $key => $label)
                                    <option value="{{ $key }}" 
                                            {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nomor Permohonan</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th width="15%">Progress</th>
                                    <th width="8%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permohonan as $index => $item)
                                <tr>
                                    <td>{{ $index + $permohonan->firstItem() }}</td>
                                    <td>
                                        <strong>{{ $item->nomor_permohonan ?? '-' }}</strong>
                                    </td>
                                    <td>{{ $item->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                    <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                    <td>{{ str()->limit($item->nama_dokumen, 30) }}</td>
                                    <td>{{ $item->getTanggalPermohonanFormattedAttribute() }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $currentStep = $item->getCurrentStepIndex();
                                            $totalSteps = 7;
                                            $percentage = ($currentStep / ($totalSteps - 1)) * 100;
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $item->status === 'rejected' ? 'danger' : ($percentage >= 100 ? 'success' : 'primary') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%"
                                                     aria-valuenow="{{ $percentage }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ round($percentage) }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('permohonan.show', $item) }}">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                                @if($item->status == 'draft' && auth()->user()->hasRole('kabupaten_kota'))
                                                <a class="dropdown-item" href="{{ route('permohonan.edit', $item) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('permohonan.destroy', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" 
                                                            onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                                @endif
                                                @if($item->status == 'draft' && auth()->user()->hasRole('kabupaten_kota'))
                                                <form action="{{ route('permohonan.submit', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bx bx-send me-1"></i> Submit
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada data permohonan</td>
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