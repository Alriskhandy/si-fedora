@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Buat Undangan Pelaksanaan</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('undangan-pelaksanaan.index') }}">Undangan Pelaksanaan</a></li>
                        <li class="breadcrumb-item active">Buat Undangan</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('undangan-pelaksanaan.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <!-- Informasi Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kabupaten/Kota</dt>
                            <dd class="col-sm-7">{{ $permohonan->kabupatenKota->nama }}</dd>

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $permohonan->no_permohonan }}</dd>

                            <dt class="col-sm-5">Tanggal</dt>
                            <dd class="col-sm-7">{{ $permohonan->created_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Jadwal Fasilitasi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Jadwal Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        @if ($permohonan->penetapanJadwal)
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Tanggal Mulai</dt>
                                <dd class="col-sm-7">
                                    {{ $permohonan->penetapanJadwal->tanggal_mulai->format('d M Y') }}</dd>

                                <dt class="col-sm-5">Tanggal Selesai</dt>
                                <dd class="col-sm-7">
                                    {{ $permohonan->penetapanJadwal->tanggal_selesai->format('d M Y') }}</dd>

                                <dt class="col-sm-5">Lokasi</dt>
                                <dd class="col-sm-7">{{ $permohonan->penetapanJadwal->lokasi ?? '-' }}</dd>

                                <dt class="col-sm-5">Durasi</dt>
                                <dd class="col-sm-7">{{ $permohonan->penetapanJadwal->durasi_hari }} hari</dd>
                            </dl>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Form Undangan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('undangan-pelaksanaan.store', $permohonan) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="file_undangan">File Undangan (PDF) <span
                                        class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file_undangan" name="file_undangan"
                                    accept=".pdf" required>
                                <small class="text-muted">Format: PDF, Maksimal 2MB</small>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">
                                <i class='bx bx-user-check text-primary'></i> Penerima Undangan (Tim yang Di-assign)
                            </h6>

                            @if($verifikatorList->isEmpty() && $fasilitatorList->isEmpty() && $koordinatorList->isEmpty())
                                <div class="alert alert-warning">
                                    <i class='bx bx-error-circle'></i>
                                    <strong>Peringatan:</strong> Belum ada tim FEDORA yang di-assign untuk {{ $permohonan->kabupatenKota->nama }} tahun {{ $permohonan->tahun }}.
                                    Silakan assign tim terlebih dahulu di menu Tim Assignment.
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class='bx bx-info-circle'></i>
                                    <small>Tim yang di-assign ke {{ $permohonan->kabupatenKota->nama }} otomatis dipilih.</small>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Verifikator -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class='bx bx-check-shield text-success'></i> Verifikator
                                    </label>
                                    <div class="border rounded p-3" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
                                        @forelse ($verifikatorList as $verifikator)
                                            @php
                                                $assignment = $verifikator->kabkotaAssignments->first();
                                            @endphp
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="penerima[]"
                                                    value="{{ $verifikator->id }}" id="ver_{{ $verifikator->id }}"
                                                    {{ in_array($verifikator->id, $autoSelectedPenerima) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ver_{{ $verifikator->id }}">
                                                    <strong>{{ $verifikator->name }}</strong>
                                                    @if($assignment && $assignment->is_pic)
                                                        <span class="badge bg-primary badge-sm">PIC</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $verifikator->email }}</small>
                                                    @if($assignment && $assignment->nomor_surat)
                                                        <br>
                                                        <small class="text-muted"><i class='bx bx-file'></i> {{ $assignment->nomor_surat }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0 small"><i class='bx bx-info-circle'></i> Tidak ada verifikator</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Fasilitator -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class='bx bx-briefcase text-info'></i> Fasilitator
                                    </label>
                                    <div class="border rounded p-3" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
                                        @forelse ($fasilitatorList as $fasilitator)
                                            @php
                                                $assignment = $fasilitator->kabkotaAssignments->first();
                                            @endphp
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="penerima[]"
                                                    value="{{ $fasilitator->id }}" id="fas_{{ $fasilitator->id }}"
                                                    {{ in_array($fasilitator->id, $autoSelectedPenerima) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="fas_{{ $fasilitator->id }}">
                                                    <strong>{{ $fasilitator->name }}</strong>
                                                    @if($assignment && $assignment->is_pic)
                                                        <span class="badge bg-primary badge-sm">PIC</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $fasilitator->email }}</small>
                                                    @if($assignment && $assignment->nomor_surat)
                                                        <br>
                                                        <small class="text-muted"><i class='bx bx-file'></i> {{ $assignment->nomor_surat }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0 small"><i class='bx bx-info-circle'></i> Tidak ada fasilitator</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Pemohon -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class='bx bx-user text-secondary'></i> Pemohon ({{ $permohonan->kabupatenKota->nama }})
                                    </label>
                                    <div class="border rounded p-3">
                                        <div class="row">
                                            @forelse ($pemohonList as $pemohon)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="penerima[]"
                                                            value="{{ $pemohon->id }}" id="pem_{{ $pemohon->id }}"
                                                            {{ in_array($pemohon->id, $autoSelectedPenerima) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="pem_{{ $pemohon->id }}">
                                                            <strong>{{ $pemohon->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $pemohon->email }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <p class="text-muted mb-0 small"><i class='bx bx-info-circle'></i> Tidak ada pemohon dari kabupaten/kota ini</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-send me-1"></i> Kirim Undangan
                                </button>
                                <a href="{{ route('undangan-pelaksanaan.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
