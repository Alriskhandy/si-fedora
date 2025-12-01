@extends('layouts.app')

@section('title', 'Evaluasi Permohonan')

@section('styles')
<link href="https://cdn.ckeditor.com/4.16.2/standard/contents.css" rel="stylesheet">
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Evaluasi Permohonan</h5>
                    <a href="{{ route('evaluasi.index') }}" class="btn btn-outline-secondary">
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
                                    <td>{{ $permohonan->tanggal_permohonan->format('d M Y') ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Dokumen Persyaratan -->
                    <div class="mb-4">
                        <h6>Dokumen Persyaratan (Hasil Verifikasi)</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Status</th>
                                        <th>File</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permohonan->permohonanDokumen as $index => $dokumen)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $dokumen->persyaratanDokumen->nama ?? 'Dokumen Tidak Ditemukan' }}</td>
                                        <td>
                                            @if($dokumen->is_ada)
                                                <span class="badge bg-label-success">ADA</span>
                                            @else
                                                <span class="badge bg-label-danger">TIDAK ADA</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dokumen->file_path)
                                                <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-download me-1"></i> Lihat
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $dokumen->catatan_verifikasi ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada dokumen persyaratan</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Form Evaluasi -->
                    <form action="{{ route('evaluasi.store', $permohonan) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label" for="draft_rekomendasi">Draft Rekomendasi</label>
                            <textarea class="form-control" id="draft_rekomendasi" name="draft_rekomendasi" rows="8" required>{{ old('draft_rekomendasi', $evaluasi?->draft_rekomendasi) }}</textarea>
                            <div class="form-text">Masukkan draft rekomendasi hasil evaluasi dokumen.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="file_draft">Upload File Draft Rekomendasi</label>
                            <input type="file" class="form-control" id="file_draft" name="file_draft" accept=".pdf,.doc,.docx">
                            @if($evaluasi?->file_draft)
                                <div class="mt-2">
                                    <a href="{{ route('evaluasi.download-draft', $evaluasi) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-download me-1"></i> Lihat File Draft Saat Ini
                                    </a>
                                </div>
                            @endif
                            <div class="form-text">Format: PDF, DOC, DOCX | Max: 10MB</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="catatan_evaluasi">Catatan Evaluasi</label>
                            <textarea class="form-control" id="catatan_evaluasi" name="catatan_evaluasi" rows="4">{{ old('catatan_evaluasi', $evaluasi?->catatan_evaluasi) }}</textarea>
                            <div class="form-text">Catatan tambahan atau masukan khusus dari tim evaluasi.</div>
                        </div>

                        <button type="submit" class="btn btn-success me-2">
                            <i class="bx bx-save me-1"></i> Simpan Draft Rekomendasi
                        </button>
                        <a href="{{ route('evaluasi.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i> Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('draft_rekomendasi');
</script>
@endsection