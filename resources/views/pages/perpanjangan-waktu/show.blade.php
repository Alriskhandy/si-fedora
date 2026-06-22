@extends('layouts.app')

@section('title', 'Detail Perpanjangan Waktu')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Perpanjangan Waktu</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('perpanjangan-waktu.index') }}">Perpanjangan Waktu</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('perpanjangan-waktu.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7">
                <!-- Informasi Pengajuan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class='bx bx-file me-2'></i>Informasi Pengajuan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="30%">Tanggal :</th>
                                <td>{{ $perpanjanganWaktu->created_at->format('d F Y, H:i') }} WIT</td>
                            </tr>
                            <tr>
                                <th>Diajukan Oleh :</th>
                                <td>{{ $perpanjanganWaktu->user->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alasan :</th>
                                <td>{{ $perpanjanganWaktu->alasan }}</td>
                            </tr>
                            <tr>
                                <th>Surat :</th>
                                <td>
                                    @if ($perpanjanganWaktu->surat_permohonan)
                                        <a href="{{ route('perpanjangan-waktu.download', $perpanjanganWaktu) }}"
                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class='bx bx-download me-1'></i>Download Surat
                                        </a>
                                    @else
                                        <span class="text-muted">Belum diupload</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Status Pemrosesan -->
                @if ($perpanjanganWaktu->diproses_at)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class='bx bx-check-shield me-2'></i>Hasil Pemrosesan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="30%">Status :</th>
                                    <td><span class="badge bg-success">Telah Diproses</span></td>
                                </tr>
                                <tr>
                                    <th>Diproses Oleh :</th>
                                    <td>{{ $perpanjanganWaktu->admin->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Proses :</th>
                                    <td>{{ $perpanjanganWaktu->diproses_at->format('d F Y, H:i') }} WIT</td>
                                </tr>
                                @if ($perpanjanganWaktu->batas_waktu)
                                    <tr>
                                        <th>Batas Waktu Baru :</th>
                                        <td>
                                            <strong class="text-primary">
                                                <i class='bx bx-calendar me-1'></i>{{ $perpanjanganWaktu->batas_waktu->format('d F Y, H:i') }} WIT
                                            </strong>
                                        </td>
                                    </tr>
                                @endif
                                @if ($perpanjanganWaktu->catatan_admin)
                                    <tr>
                                        <th>Catatan Admin :</th>
                                        <td>{{ $perpanjanganWaktu->catatan_admin }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class='bx bx-time-five me-2'></i>
                        Permohonan perpanjangan sedang menunggu diproses oleh admin.
                    </div>

                    @if (auth()->user()->hasAnyRole(['admin_peran', 'superadmin']))
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class='bx bx-cog me-2'></i>Proses Perpanjangan</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('perpanjangan-waktu.process', $perpanjanganWaktu) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    @if ($perpanjanganWaktu->permohonan->jadwalFasilitasi)
                                        <div class="alert alert-info">
                                            <i class='bx bx-calendar me-2'></i>
                                            <strong>Batas Waktu Jadwal Saat Ini:</strong>
                                            {{ \Carbon\Carbon::parse($perpanjanganWaktu->permohonan->jadwalFasilitasi->batas_permohonan)->format('d F Y, H:i') }} WIT
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label">Batas Waktu Baru <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="batas_permohonan_baru" class="form-control" required>
                                        <small class="text-muted">Batas waktu ini hanya berlaku untuk permohonan ini</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan Admin <span class="text-danger">*</span></label>
                                        <textarea name="catatan_admin" class="form-control" rows="3" required
                                            placeholder="Berikan catatan terkait perpanjangan waktu ini..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        <i class='bx bx-check-circle me-1'></i> Proses Perpanjangan
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="col-lg-5">
                <!-- Info Permohonan Terkait -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Informasi Permohonan</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="30%">Permohonan :</th>
                                <td>{{ $perpanjanganWaktu->permohonan->jenisDokumen->nama ?? '-' }} Tahun {{ $perpanjanganWaktu->permohonan->tahun }}</td>
                            </tr>
                            <tr>
                                <th>Kab / Kota :</th>
                                <td>{{ $perpanjanganWaktu->permohonan->kabupatenKota->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status :</th>
                                <td><span class="badge bg-secondary">{{ $perpanjanganWaktu->permohonan->status_akhir }}</span></td>
                            </tr>
                        </table>
                        <a href="{{ route('permohonan.show', $perpanjanganWaktu->permohonan) }}"
                            class="btn btn-outline-primary btn-sm w-100 mt-2">
                            <i class='bx bx-show me-1'></i>Lihat Detail Permohonan
                        </a>
                    </div>
                </div>

                <!-- Aksi Pemohon -->
                @if (auth()->user()->id === $perpanjanganWaktu->user_id && !$perpanjanganWaktu->diproses_at)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class='bx bx-cog me-1'></i>Aksi</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('perpanjangan-waktu.destroy', $perpanjanganWaktu) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class='bx bx-trash me-1'></i>Batalkan Permohonan
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
