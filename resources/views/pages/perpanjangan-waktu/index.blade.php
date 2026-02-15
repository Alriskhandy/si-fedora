@extends('layouts.app')

@section('title', 'Permohonan Perpanjangan Waktu')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Permohonan Perpanjangan Waktu</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Perpanjangan Waktu</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Alert Section -->
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

        <!-- Filters -->
        <div class="card mb-4" style="background-color: #f8f9fa;">
            <div class="card-body">
                <form method="GET" action="{{ route('perpanjangan-waktu.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari Permohonan</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama Kabupaten/Kota..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class='bx bx-search'></i> Filter
                            </button>
                            <a href="{{ route('perpanjangan-waktu.index') }}" class="btn btn-outline-secondary">
                                <i class='bx bx-reset'></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- List -->
        <div class="card">
            <div class="card-body">
                @if ($perpanjanganList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Deskripsi</th>
                                    <th width="15%">Tanggal & Waktu</th>
                                    <th width="20%">Alasan</th>
                                    <th width="10%">File Surat</th>
                                    <th width="15%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($perpanjanganList as $index => $perpanjangan)
                                    <tr>
                                        <td>{{ $perpanjanganList->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $perpanjangan->permohonan->jenisDokumen->nama ?? 'N/A' }}</strong><br>
                                            {{ $perpanjangan->user->name ?? '-' }}<br>
                                            <small class="text-muted">Tahun {{ $perpanjangan->permohonan->tahun }}</small>
                                        </td>
                                        <td>
                                            {{ $perpanjangan->created_at->format('d M Y') }}<br>
                                            <small class="text-muted">{{ $perpanjangan->created_at->format('H:i') }}
                                                WIT</small>
                                        </td>
                                        <td><small>{{ str()->limit($perpanjangan->alasan, 60) }}</small></td>
                                        <td>
                                            @if ($perpanjangan->surat_permohonan)
                                                <a href="{{ asset('storage/' . $perpanjangan->surat_permohonan) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class='bx bx-file-blank'></i> Lihat
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">Belum Upload</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($perpanjangan->diproses_at)
                                                <span class="badge bg-success">
                                                    <i class='bx bx-check'></i> Diproses
                                                </span>
                                                <br><small
                                                    class="text-muted">{{ $perpanjangan->diproses_at->format('d M Y, H:i') }} WIT</small>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class='bx bx-time'></i> Menunggu
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
                                                    @if (!$perpanjangan->diproses_at)
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#processModal{{ $perpanjangan->id }}"
                                                            title="Proses Perpanjangan">
                                                            <i class='bx bx-edit'></i> Proses
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#detailModal{{ $perpanjangan->id }}"
                                                            title="Lihat Detail">
                                                            <i class='bx bx-show'></i> Detail
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Process Modal -->
                                    @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
                                        <div class="modal fade" id="processModal{{ $perpanjangan->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class='bx bx-time-five me-2'></i>Proses Perpanjangan Waktu
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('perpanjangan-waktu.process', $perpanjangan) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <!-- Info Permohonan -->
                                                            <div class="alert alert-info">
                                                                <h6 class="mb-2"><i
                                                                        class='bx bx-info-circle me-2'></i>Informasi
                                                                    Permohonan</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">Jenis Dokumen:</small>
                                                                        <p class="mb-1">
                                                                            <strong>{{ $perpanjangan->permohonan->jenisDokumen->nama ?? '-' }}</strong>
                                                                        </p>
                                                                        <small class="text-muted">Pemohon:</small>
                                                                        <p class="mb-1">{{ $perpanjangan->user->name }}
                                                                            ({{ $perpanjangan->permohonan->kabupatenKota->nama ?? '-' }})
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">Tanggal Pengajuan:</small>
                                                                        <p class="mb-1">
                                                                            {{ $perpanjangan->created_at->format('d F Y, H:i') }}
                                                                            WIB</p>
                                                                        <small class="text-muted">Alasan:</small>
                                                                        <p class="mb-0">{{ $perpanjangan->alasan }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Batas Waktu Saat Ini -->
                                                            @if ($perpanjangan->permohonan->jadwalFasilitasi)
                                                                <div class="alert alert-warning">
                                                                    <i class='bx bx-calendar me-2'></i>
                                                                    <strong>Batas Upload Dokumen Saat Ini:</strong><br>
                                                                    {{ \Carbon\Carbon::parse($perpanjangan->permohonan->jadwalFasilitasi->batas_permohonan)->format('d F Y, H:i') }}
                                                                    WIB
                                                                </div>
                                                            @endif

                                                            <!-- Form Update Batas Waktu -->
                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    Batas Waktu Upload Baru <span
                                                                        class="text-danger">*</span>
                                                                </label>
                                                                <input type="datetime-local" name="batas_permohonan_baru"
                                                                    class="form-control"
                                                                    value="{{ $perpanjangan->permohonan->jadwalFasilitasi ? \Carbon\Carbon::parse($perpanjangan->permohonan->jadwalFasilitasi->batas_permohonan)->format('Y-m-d\TH:i') : '' }}"
                                                                    required>
                                                                <small class="text-muted">Tentukan batas waktu baru untuk
                                                                    upload dokumen</small>
                                                            </div>

                                                            <!-- Catatan Admin -->
                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    Catatan Admin <span class="text-danger">*</span>
                                                                </label>
                                                                <textarea name="catatan_admin" class="form-control" rows="3"
                                                                    placeholder="Berikan catatan terkait perpanjangan waktu ini..." required>{{ old('catatan_admin') }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                                <i class='bx bx-x'></i> Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class='bx bx-save me-1'></i> Simpan Perpanjangan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <!-- Detail Modal (for processed requests) -->
                                @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
                                    @foreach ($perpanjanganList as $perpanjangan)
                                        @if ($perpanjangan->diproses_at)
                                            <div class="modal fade" id="detailModal{{ $perpanjangan->id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info">
                                                            <h5 class="modal-title text-white">
                                                                <i class='bx bx-detail me-2'></i>Detail Perpanjangan Waktu
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Info Permohonan -->
                                                            <div class="alert alert-info">
                                                                <h6 class="mb-2"><i
                                                                        class='bx bx-info-circle me-2'></i>Informasi
                                                                    Permohonan</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">Jenis Dokumen:</small>
                                                                        <p class="mb-1">
                                                                            <strong>{{ $perpanjangan->permohonan->jenisDokumen->nama ?? '-' }}</strong>
                                                                        </p>
                                                                        <small class="text-muted">Pemohon:</small>
                                                                        <p class="mb-1">{{ $perpanjangan->user->name }}
                                                                            ({{ $perpanjangan->permohonan->kabupatenKota->nama ?? '-' }})
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">Tanggal
                                                                            Pengajuan:</small>
                                                                        <p class="mb-1">
                                                                            {{ $perpanjangan->created_at->format('d F Y, H:i') }}
                                                                            WIB</p>
                                                                        <small class="text-muted">Alasan:</small>
                                                                        <p class="mb-0">{{ $perpanjangan->alasan }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Status Perpanjangan -->
                                                            <div class="alert alert-success">
                                                                <i class='bx bx-check-circle me-2'></i>
                                                                <strong>Status:</strong> Telah Diproses<br>
                                                                <small class="text-muted">Diproses pada:
                                                                    {{ $perpanjangan->diproses_at->format('d F Y, H:i') }}
                                                                    WIB</small><br>
                                                                <small class="text-muted">Diproses oleh:
                                                                    {{ $perpanjangan->diproses_oleh_user->name ?? '-' }}</small>
                                                            </div>

                                                            <!-- Batas Waktu Baru -->
                                                            @if ($perpanjangan->permohonan->jadwalFasilitasi)
                                                                <div class="mb-3">
                                                                    <label class="form-label"><strong>Batas Waktu Upload
                                                                            Baru:</strong></label>
                                                                    <p class="text-primary">
                                                                        <i class='bx bx-calendar me-2'></i>
                                                                        {{ \Carbon\Carbon::parse($perpanjangan->permohonan->jadwalFasilitasi->batas_permohonan)->format('d F Y, H:i') }}
                                                                        WIB
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            <!-- Catatan Admin -->
                                                            @if ($perpanjangan->catatan_admin)
                                                                <div class="mb-3">
                                                                    <label class="form-label"><strong>Catatan
                                                                            Admin:</strong></label>
                                                                    <div class="p-3 bg-light rounded">
                                                                        {{ $perpanjangan->catatan_admin }}
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                                <i class='bx bx-x'></i> Tutup
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $perpanjanganList->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class='bx bx-time-five bx-lg text-muted mb-3 d-block'></i>
                        <h5 class="text-muted">Belum ada permohonan perpanjangan waktu</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
