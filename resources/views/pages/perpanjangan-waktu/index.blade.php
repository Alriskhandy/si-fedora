@extends('layouts.app')

@section('title', 'Perpanjangan Waktu')

@section('main')
    @php
        $isPemohon = auth()->user()->hasRole('pemohon');
        $isAdmin = auth()->user()->hasAnyRole(['admin_peran', 'superadmin']);
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Perpanjangan Waktu</h4>
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

        <!-- Permohonan Lewat Batas Waktu (Pemohon only) -->
        @if ($isPemohon && $permohonanLewatBatas->count() > 0)
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0 text-white"><i class='bx bx-error-circle me-2'></i>Permohonan Melewati Batas Waktu</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Permohonan berikut telah melewati batas waktu upload dokumen. Ajukan perpanjangan waktu agar dapat melanjutkan.</p>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Permohonan</th>
                                    <th>Kab / Kota</th>
                                    <th>Batas Waktu</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permohonanLewatBatas as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $p->jenisDokumen->nama ?? '-' }} Tahun {{ $p->tahun }}</td>
                                        <td>{{ $p->kabupatenKota->nama ?? '-' }}</td>
                                        <td>
                                            @php $deadline = $p->getEffectiveDeadline(); @endphp
                                            @if ($deadline)
                                                <strong class="text-danger">
                                                    {{ \Carbon\Carbon::parse($deadline)->format('d M Y, H:i') }} WIT
                                                </strong>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('perpanjangan-waktu.create', ['permohonan_id' => $p->id]) }}"
                                                class="btn btn-sm btn-warning">
                                                <i class='bx bx-time-five me-1'></i>Ajukan Perpanjangan
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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
                            <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari</label>
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
            <div class="card-header">
                <h5 class="mb-0"><i class='bx bx-list-ul me-2'></i>Daftar Pengajuan</h5>
            </div>
            <div class="card-body">
                @if ($perpanjanganList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Permohonan</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Surat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($perpanjanganList as $index => $perpanjangan)
                                    <tr>
                                        <td>{{ $perpanjanganList->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $perpanjangan->permohonan->jenisDokumen->nama ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">
                                                {{ $perpanjangan->permohonan->kabupatenKota->nama ?? '-' }}
                                                &bull; Tahun {{ $perpanjangan->permohonan->tahun }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ $perpanjangan->created_at->format('d M Y') }}<br>
                                            <small class="text-muted">{{ $perpanjangan->created_at->format('H:i') }} WIT</small>
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
                                                @if ($perpanjangan->batas_waktu)
                                                    <br><small class="text-primary">
                                                        s/d {{ $perpanjangan->batas_waktu->format('d M Y, H:i') }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class='bx bx-time'></i> Menunggu
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('perpanjangan-waktu.show', $perpanjangan) }}"
                                                    class="btn btn-sm btn-outline-info" title="Detail">
                                                    <i class='bx bx-show'></i>
                                                </a>

                                                @if ($isAdmin && !$perpanjangan->diproses_at)
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#processModal{{ $perpanjangan->id }}"
                                                        title="Proses">
                                                        <i class='bx bx-check-circle'></i>
                                                    </button>
                                                @endif

                                                @if ($isPemohon && !$perpanjangan->diproses_at)
                                                    <form action="{{ route('perpanjangan-waktu.destroy', $perpanjangan) }}" method="POST"
                                                        onsubmit="return confirm('Yakin ingin membatalkan permohonan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan">
                                                            <i class='bx bx-trash'></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

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

    <!-- Process Modals (Admin only) -->
    @if ($isAdmin)
        @foreach ($perpanjanganList as $perpanjangan)
            @if (!$perpanjangan->diproses_at)
                <div class="modal fade" id="processModal{{ $perpanjangan->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class='bx bx-time-five me-2'></i>Proses Perpanjangan Waktu
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('perpanjangan-waktu.process', $perpanjangan) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <table class="table table-sm table-borderless mb-3">
                                        <tr>
                                            <th width="30%">Permohonan :</th>
                                            <td>{{ $perpanjangan->permohonan->jenisDokumen->nama ?? '-' }} Tahun {{ $perpanjangan->permohonan->tahun }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kab / Kota :</th>
                                            <td>{{ $perpanjangan->user->name ?? '-' }} ({{ $perpanjangan->permohonan->kabupatenKota->nama ?? '-' }})</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Pengajuan :</th>
                                            <td>{{ $perpanjangan->created_at->format('d F Y, H:i') }} WIT</td>
                                        </tr>
                                        <tr>
                                            <th>Alasan :</th>
                                            <td>{{ $perpanjangan->alasan }}</td>
                                        </tr>
                                    </table>

                                    @if ($perpanjangan->permohonan->jadwalFasilitasi)
                                        <div class="alert alert-info">
                                            <i class='bx bx-calendar me-2'></i>
                                            <strong>Batas Waktu Jadwal Saat Ini:</strong>
                                            {{ \Carbon\Carbon::parse($perpanjangan->permohonan->jadwalFasilitasi->batas_permohonan)->format('d F Y, H:i') }} WIT
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label">Batas Waktu Baru <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="batas_permohonan_baru" class="form-control" required>
                                        <small class="text-muted">Batas waktu ini hanya berlaku untuk permohonan ini</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan Admin <span class="text-danger">*</span></label>
                                        <textarea name="catatan_admin" class="form-control" rows="3"
                                            placeholder="Berikan catatan terkait perpanjangan waktu ini..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class='bx bx-x'></i> Batal
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class='bx bx-check-circle me-1'></i> Proses Perpanjangan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endsection
