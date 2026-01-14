@extends('layouts.app')

@section('title', 'Detail Perpanjangan Waktu')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a
                            href="{{ route('permohonan.show', $perpanjanganWaktu->permohonan) }}">Detail Permohonan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('perpanjangan-waktu.index') }}">Perpanjangan Waktu</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Detail Perpanjangan Waktu</h4>
                    <p class="text-muted mb-0">ID: #{{ $perpanjanganWaktu->id }}</p>
                </div>
                <div>
                    <span class="badge bg-{{ $perpanjanganWaktu->statusBadgeClass }} fs-6">
                        {{ $perpanjanganWaktu->statusText }}
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Informasi Permohonan Perpanjangan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class='bx bx-file me-2'></i>Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Tanggal Pengajuan:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $perpanjanganWaktu->created_at->format('d F Y, H:i') }} WIB
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Diajukan Oleh:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $perpanjanganWaktu->user->name ?? '-' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Perpanjangan:</strong>
                            </div>
                            <div class="col-md-8">
                                <strong class="text-primary fs-5">{{ $perpanjanganWaktu->perpanjangan_hari }} Hari</strong>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Alasan:</strong>
                            </div>
                            <div class="col-md-8">
                                <div class="alert alert-light">
                                    {{ $perpanjanganWaktu->alasan }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Surat Permohonan:</strong>
                            </div>
                            <div class="col-md-8">
                                <a href="{{ route('perpanjangan-waktu.download', $perpanjanganWaktu) }}"
                                    class="btn btn-outline-primary" target="_blank">
                                    <i class='bx bx-download'></i> Download Surat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Pemrosesan -->
                @if ($perpanjanganWaktu->status !== 'pending')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class='bx bx-check-shield me-2'></i>Status Pemrosesan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Status:</strong>
                                </div>
                                <div class="col-md-8">
                                    <span class="badge bg-{{ $perpanjanganWaktu->statusBadgeClass }} fs-6">
                                        {{ $perpanjanganWaktu->statusText }}
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Diproses Oleh:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $perpanjanganWaktu->admin->name ?? '-' }}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Tanggal Diproses:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $perpanjanganWaktu->diproses_at ? $perpanjanganWaktu->diproses_at->format('d F Y, H:i') : '-' }}
                                    WIB
                                </div>
                            </div>
                            @if ($perpanjanganWaktu->catatan_admin)
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Catatan Admin:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <div
                                            class="alert alert-{{ $perpanjanganWaktu->status === 'approved' ? 'success' : 'danger' }}">
                                            {{ $perpanjanganWaktu->catatan_admin }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($perpanjanganWaktu->status === 'approved')
                                <div class="alert alert-success mt-3">
                                    <i class='bx bx-check-circle me-2'></i>
                                    <strong>Batas waktu upload telah diperpanjang!</strong><br>
                                    Batas waktu baru:
                                    <strong>{{ \Carbon\Carbon::parse($perpanjanganWaktu->permohonan->jadwalFasilitasi->batas_permohonan)->format('d F Y, H:i') }}
                                        WIB</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class='bx bx-time-five me-2'></i>
                        Permohonan perpanjangan sedang menunggu persetujuan admin. Anda akan mendapat notifikasi setelah
                        permohonan diproses.
                    </div>

                    @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class='bx bx-cog me-2'></i>Aksi Admin</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#approveModal">
                                        <i class='bx bx-check-circle'></i> Setujui Permohonan
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        <i class='bx bx-x-circle'></i> Tolak Permohonan
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Info Permohonan Terkait -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Permohonan Terkait</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="45%">Kabupaten/Kota:</th>
                                <td><strong>{{ $perpanjanganWaktu->permohonan->kabupatenKota->nama ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Tahun:</th>
                                <td>{{ $perpanjanganWaktu->permohonan->tahun }}</td>
                            </tr>
                            <tr>
                                <th>Status Permohonan:</th>
                                <td>
                                    <span class="badge bg-{{ $perpanjanganWaktu->permohonan->statusBadgeClass }}">
                                        {{ $perpanjanganWaktu->permohonan->status_akhir }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        <a href="{{ route('permohonan.show', $perpanjanganWaktu->permohonan) }}"
                            class="btn btn-outline-primary btn-sm w-100">
                            <i class='bx bx-show'></i> Lihat Detail Permohonan
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                @if (auth()->user()->id === $perpanjanganWaktu->user_id && $perpanjanganWaktu->status === 'pending')
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">Aksi</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Batalkan permohonan perpanjangan ini?</p>
                            <form action="{{ route('perpanjangan-waktu.destroy', $perpanjanganWaktu) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class='bx bx-trash'></i> Batalkan Permohonan
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']) && $perpanjanganWaktu->status === 'pending')
        <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Setujui Perpanjangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('perpanjangan-waktu.update-status', $perpanjanganWaktu) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <div class="modal-body">
                            <div class="alert alert-success">
                                <i class='bx bx-check-circle me-2'></i>
                                Anda akan menyetujui perpanjangan <strong>{{ $perpanjanganWaktu->perpanjangan_hari }}
                                    hari</strong>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class='bx bx-check'></i> Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Perpanjangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('perpanjangan-waktu.update-status', $perpanjanganWaktu) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <i class='bx bx-error me-2'></i>
                                Anda akan menolak permohonan perpanjangan ini
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="catatan_admin" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan"></textarea>
                                <small class="text-muted">Alasan ini akan disampaikan kepada pemohon</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class='bx bx-x'></i> Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
