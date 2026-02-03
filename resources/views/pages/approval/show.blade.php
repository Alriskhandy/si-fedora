@extends('layouts.app')

@section('title', 'Review Draft Rekomendasi')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Review Draft Rekomendasi</h5>
                    <a href="{{ route('approval.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <!-- Info Permohonan -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Permohonan</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nomor Permohonan</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->nomor_permohonan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kabupaten/Kota</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Dokumen</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Permohonan</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->created_at->format('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Draft Rekomendasi -->
                    <div class="mb-4">
                        <h6>Draft Rekomendasi dari Tim Pokja</h6>
                        @if($permohonan->evaluasi)
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($permohonan->evaluasi->draft_rekomendasi)) !!}
                                
                                @if($permohonan->evaluasi->file_draft)
                                <div class="mt-3">
                                    <a href="{{ route('approval.download-draft', $permohonan->evaluasi) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-download me-1"></i> Download File Draft
                                    </a>
                                </div>
                                @endif

                                @if($permohonan->evaluasi->catatan_evaluasi)
                                <div class="mt-3">
                                    <strong>Catatan Evaluasi:</strong><br>
                                    {{ $permohonan->evaluasi->catatan_evaluasi }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            Draft rekomendasi belum tersedia.
                        </div>
                        @endif
                    </div>

                    <!-- Form Approval -->
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('approval.approve', $permohonan) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bx bx-check-circle me-1"></i> Approve Draft
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bx bx-x-circle me-1"></i> Reject Draft
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Draft Rekomendasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('approval.reject', $permohonan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Catatan Penolakan</label>
                        <textarea class="form-control" name="catatan_penolakan" rows="3" required 
                                  placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection