@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Validasi Hasil Fasilitasi</h4>
            <div>
                @if ($hasilFasilitasi && ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                    <a href="{{ route('validasi-hasil.generate', $permohonan->id) }}" class="btn btn-success me-2">
                        <i class="bx bx-file"></i> Generate Word
                    </a>
                    <a href="{{ route('validasi-hasil.generate-pdf', $permohonan->id) }}" class="btn btn-primary me-2">
                        <i class="bx bxs-file-pdf"></i> Generate PDF
                    </a>
                @endif
                <a href="{{ route('validasi-hasil.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

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

                <!-- Status Draft -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Draft</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">
                                @if ($hasilFasilitasi->status_draft == 'diajukan')
                                    <span class="badge bg-info">Perlu Validasi</span>
                                @elseif($hasilFasilitasi->status_draft == 'revisi')
                                    <span class="badge bg-warning">Revisi</span>
                                @else
                                    <span class="badge bg-success">Disetujui</span>
                                @endif
                            </dd>

                            <dt class="col-sm-5">Fasilitator</dt>
                            <dd class="col-sm-7">{{ $hasilFasilitasi->pembuat->name }}</dd>

                            <dt class="col-sm-5">Tanggal Dibuat</dt>
                            <dd class="col-sm-7">
                                {{ $hasilFasilitasi->tanggal_dibuat ? $hasilFasilitasi->tanggal_dibuat->format('d M Y H:i') : '-' }}
                            </dd>

                            <dt class="col-sm-5">Tanggal Diajukan</dt>
                            <dd class="col-sm-7">
                                {{ $hasilFasilitasi->tanggal_diajukan ? $hasilFasilitasi->tanggal_diajukan->format('d M Y H:i') : '-' }}
                            </dd>

                            @if ($hasilFasilitasi->validator)
                                <dt class="col-sm-5">Validator</dt>
                                <dd class="col-sm-7">{{ $hasilFasilitasi->validator->name }}</dd>

                                <dt class="col-sm-5">Tanggal Validasi</dt>
                                <dd class="col-sm-7">
                                    {{ $hasilFasilitasi->tanggal_validasi ? $hasilFasilitasi->tanggal_validasi->format('d M Y H:i') : '-' }}
                                </dd>
                            @endif
                        </dl>

                        @if ($hasilFasilitasi->isDiajukan())
                            <hr>
                            <h6 class="mb-3">Validasi</h6>

                            <!-- Form Setujui -->
                            <form method="POST" action="{{ route('validasi-hasil.approve', $permohonan) }}" class="mb-2"
                                onsubmit="return confirm('Setujui hasil fasilitasi ini?')">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Catatan (opsional)</label>
                                    <textarea class="form-control" name="catatan_validasi" rows="2" placeholder="Catatan persetujuan..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bx bx-check"></i> Setujui
                                </button>
                            </form>

                            <!-- Form Revisi -->
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal"
                                data-bs-target="#revisiModal">
                                <i class="bx bx-revision"></i> Minta Revisi
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Hasil Fasilitasi -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Hasil Fasilitasi</h5>
                        @if ($hasilFasilitasi->file_draft)
                            <a href="{{ route('hasil-fasilitasi.download', $permohonan) }}" class="btn btn-sm btn-info">
                                <i class="bx bx-download"></i> Download Draft
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6>Ringkasan Hasil</h6>
                        <div class="border rounded p-3 mb-3 bg-light" style="white-space: pre-line;">
                            {{ $hasilFasilitasi->ringkasan_hasil }}</div>

                        <h6>Rekomendasi</h6>
                        <div class="border rounded p-3 mb-3 bg-light" style="white-space: pre-line;">
                            {{ $hasilFasilitasi->rekomendasi }}</div>

                        @if ($hasilFasilitasi->catatan_fasilitator)
                            <h6>Catatan Fasilitator</h6>
                            <div class="border rounded p-3 mb-3 bg-light" style="white-space: pre-line;">
                                {{ $hasilFasilitasi->catatan_fasilitator }}</div>
                        @endif

                        @if ($hasilFasilitasi->catatan_validasi)
                            <h6>Catatan Validasi Sebelumnya</h6>
                            <div class="alert alert-{{ $hasilFasilitasi->isRevisi() ? 'warning' : 'info' }} mb-0">
                                {{ $hasilFasilitasi->catatan_validasi }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pembahasan Per Urusan -->
                @if ($hasilFasilitasi->hasilUrusan->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Pembahasan Per Urusan</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($hasilFasilitasi->hasilUrusan as $urusan)
                                <div class="mb-4">
                                    <h6 class="text-primary">{{ $urusan->masterUrusan->urutan }}.
                                        {{ $urusan->masterUrusan->nama_urusan }}</h6>

                                    @if ($urusan->pembahasan)
                                        <div class="mb-2">
                                            <strong>Pembahasan:</strong>
                                            <div class="border rounded p-2 mt-1 bg-light" style="white-space: pre-line;">
                                                {{ $urusan->pembahasan }}</div>
                                        </div>
                                    @endif

                                    @if ($urusan->rekomendasi_urusan)
                                        <div>
                                            <strong>Rekomendasi:</strong>
                                            <div class="border rounded p-2 mt-1 bg-warning bg-opacity-10"
                                                style="white-space: pre-line;">
                                                {{ $urusan->rekomendasi_urusan }}</div>
                                        </div>
                                    @endif
                                </div>

                                @if (!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Revisi -->
    <div class="modal fade" id="revisiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('validasi-hasil.revise', $permohonan) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Minta Revisi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="catatan_validasi" rows="4" required
                                placeholder="Jelaskan apa yang perlu direvisi..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-revision"></i> Kirim Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
