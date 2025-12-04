@extends('layouts.app')

@section('title', 'Detail Permohonan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Permohonan</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Progress Tracker -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Progress Tahapan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $steps = $permohonan->getProgressSteps();
                        $currentIndex = $permohonan->getCurrentStepIndex();
                    @endphp

                    @foreach ($steps as $index => $step)
                        <div class="col-lg-{{ 12 / count($steps) }} col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <div
                                        class="avatar avatar-lg {{ $step['completed'] ? ($permohonan->status === 'rejected' && $index === count($steps) - 1 ? 'bg-label-danger' : 'bg-label-success') : 'bg-label-secondary' }} mx-auto">
                                        <i class='bx {{ $step['icon'] }} bx-sm'></i>
                                    </div>
                                </div>
                                <h6 class="mb-1 {{ $step['completed'] ? 'text-dark' : 'text-muted' }}">{{ $step['name'] }}
                                </h6>
                                <small class="text-muted d-block mb-1">{{ $step['description'] }}</small>
                                @if ($step['date'])
                                    <small class="badge bg-label-{{ $step['completed'] ? 'primary' : 'secondary' }}">
                                        {{ $step['date']->format('d M Y') }}
                                    </small>
                                @endif
                            </div>

                            @if ($index < count($steps) - 1)
                                <div class="d-none d-lg-block position-relative"
                                    style="margin-top: -40px; margin-bottom: -40px;">
                                    <hr class="border-2 {{ $steps[$index + 1]['completed'] ? 'border-success' : 'border-secondary' }}"
                                        style="opacity: 0.5;">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($permohonan->status === 'revision_required')
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class='bx bx-error-circle me-2'></i>
                        <strong>Perlu Revisi:</strong> Silakan perbaiki dokumen sesuai catatan verifikasi.
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                        <span
                            class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Nomor Permohonan</label></div>
                            <div class="col-sm-8"><strong>{{ $permohonan->nomor_permohonan ?? '-' }}</strong></div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Kabupaten/Kota</label></div>
                            <div class="col-sm-8">
                                <strong>{{ $permohonan->kabupatenKota->getFullNameAttribute() ?? '-' }}</strong>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Jenis Dokumen</label></div>
                            <div class="col-sm-8"><strong>{{ $permohonan->jenisDokumen->nama ?? '-' }}</strong></div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Nama Dokumen</label></div>
                            <div class="col-sm-8"><strong>{{ $permohonan->nama_dokumen }}</strong></div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Tahun Anggaran</label></div>
                            <div class="col-sm-8"><strong>{{ $permohonan->tahunAnggaran->tahun ?? '-' }}</strong></div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Jadwal Fasilitasi</label></div>
                            <div class="col-sm-8">
                                <strong>{{ $permohonan->jadwalFasilitasi->nama_kegiatan ?? '-' }}</strong>
                                @if ($permohonan->jadwalFasilitasi)
                                    <br><small class="text-muted">
                                        {{ $permohonan->jadwalFasilitasi->tanggal_mulai->format('d M Y') }} -
                                        {{ $permohonan->jadwalFasilitasi->tanggal_selesai->format('d M Y') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row mb-3">
                            <div class="col-sm-4"><label class="text-muted">Tanggal Permohonan</label></div>
                            <div class="col-sm-8">
                                <strong>{{ $permohonan->tanggal_permohonan ? $permohonan->tanggal_permohonan->format('d M Y') : '-' }}</strong>
                            </div>
                        </div>
                        @if ($permohonan->keterangan)
                            <hr class="my-3">
                            <div class="row mb-3">
                                <div class="col-sm-4"><label class="text-muted">Keterangan</label></div>
                                <div class="col-sm-8">{{ $permohonan->keterangan }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Timeline Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Timeline</h5>
                    </div>
                    <div class="card-body">
                        <ul class="timeline">
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Dibuat</h6>
                                        <small
                                            class="text-muted">{{ $permohonan->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <p class="mb-0 small">Permohonan dibuat</p>
                                </div>
                            </li>
                            @if ($permohonan->submitted_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-warning"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Diajukan</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->submitted_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Menunggu verifikasi</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->verified_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Diverifikasi</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->verified_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Dokumen terverifikasi</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->assigned_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-info"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Ditugaskan</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->assigned_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Ditugaskan ke tim evaluasi</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->evaluated_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-warning"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Dievaluasi</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->evaluated_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Draft evaluasi dibuat</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->approved_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Disetujui</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->approved_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Disetujui oleh Kepala Badan</p>
                                    </div>
                                </li>
                            @endif
                            @if ($permohonan->completed_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Selesai</h6>
                                            <small
                                                class="text-muted">{{ $permohonan->completed_at->format('d M Y, H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small">Proses fasilitasi selesai</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Action Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Aksi</h5>
                    </div>
                    <div class="card-body">
                        @if ($permohonan->status == 'draft' && auth()->user()->hasRole('pemohon'))
                            <a href="{{ route('permohonan.edit', $permohonan) }}" class="btn btn-primary w-100 mb-2">
                                <i class="bx bx-edit-alt me-1"></i> Edit Permohonan
                            </a>
                            <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Yakin ingin mengirim permohonan ini?')">
                                    <i class='bx bx-send me-1'></i> Kirim Permohonan
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('permohonan.index') }}" class="btn btn-outline-secondary w-100">
                            <i class='bx bx-arrow-back me-1'></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumen Persyaratan -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Dokumen Persyaratan</h5>
                    </div>
                    <div class="card-body">
                        @if ($permohonan->status == 'draft')
                            <div class="alert alert-info">
                                <i class='bx bx-info-circle me-2'></i>
                                Silakan upload dokumen persyaratan sebelum mengirim permohonan.
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Dokumen</th>
                                        <th>File</th>
                                        <th width="10%">Status</th>
                                        <th>Catatan Verifikasi</th>
                                        @if ($permohonan->status == 'draft' || $permohonan->status == 'revision_required')
                                            <th width="10%">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permohonan->permohonanDokumen as $index => $dokumen)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>
                                                    <strong>{{ $dokumen->persyaratanDokumen->nama ?? 'Dokumen Persyaratan' }}</strong>
                                                    @if ($dokumen->persyaratanDokumen && $dokumen->persyaratanDokumen->is_wajib)
                                                        <span class="badge badge-sm bg-label-danger ms-1">Wajib</span>
                                                    @endif
                                                </div>
                                                @if ($dokumen->persyaratanDokumen && $dokumen->persyaratanDokumen->deskripsi)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class='bx bx-info-circle'></i>
                                                        {{ $dokumen->persyaratanDokumen->deskripsi }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->file_path)
                                                    <a href="{{ asset('storage/' . $dokumen->file_path) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-download me-1"></i> Lihat File
                                                    </a>
                                                    <br>
                                                    <small class="text-muted">{{ $dokumen->file_name ?? '' }}</small>
                                                @else
                                                    <span class="text-muted">Belum diupload</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->is_ada)
                                                    <span class="badge bg-label-success">
                                                        <i class='bx bx-check'></i> Ada
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger">
                                                        <i class='bx bx-x'></i> Belum
                                                    </span>
                                                @endif
                                                @if ($dokumen->status_verifikasi && $dokumen->status_verifikasi !== 'pending')
                                                    <br>
                                                    <small
                                                        class="badge bg-label-{{ $dokumen->status_verifikasi === 'verified' ? 'success' : 'warning' }} mt-1">
                                                        {{ ucfirst($dokumen->status_verifikasi) }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dokumen->catatan_verifikasi)
                                                    <small
                                                        class="text-{{ $dokumen->status_verifikasi === 'verified' ? 'success' : 'danger' }}">
                                                        <i
                                                            class='bx bx-{{ $dokumen->status_verifikasi === 'verified' ? 'check-circle' : 'error-circle' }}'></i>
                                                        {{ $dokumen->catatan_verifikasi }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @if ($permohonan->status == 'draft' || $permohonan->status == 'revision_required')
                                                <td>
                                                    <a href="{{ route('permohonan-dokumen.edit', $dokumen) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-upload me-1"></i> Upload
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $permohonan->status == 'draft' || $permohonan->status == 'revision_required' ? '6' : '5' }}"
                                                class="text-center text-muted py-4">
                                                <i class='bx bx-folder-open bx-lg mb-2 d-block'></i>
                                                Belum ada dokumen persyaratan yang didefinisikan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
