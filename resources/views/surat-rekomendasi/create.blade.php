@extends('layouts.app')

@section('title', 'Buat Surat Rekomendasi')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Buat Surat Rekomendasi</h5>
                    <a href="{{ route('surat-rekomendasi.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('surat-rekomendasi.store', $permohonan) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label" for="nomor_surat">Nomor Surat</label>
                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required 
                                   placeholder="Contoh: 001/REK/XII/2025">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="tanggal_surat">Tanggal Surat</label>
                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="perihal">Perihal</label>
                            <input type="text" class="form-control" id="perihal" name="perihal" required 
                                   placeholder="Contoh: Rekomendasi Fasilitasi Dokumen RKPD Kota Ternate 2025">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="isi_surat">Isi Surat</label>
                            <textarea class="form-control" id="isi_surat" name="isi_surat" rows="8" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="file_path">Upload File Surat</label>
                            <input type="file" class="form-control" id="file_path" name="file_path" accept=".pdf,.doc,.docx">
                            <div class="form-text">Format: PDF, DOC, DOCX | Max: 10MB</div>
                        </div>

                        <button type="submit" class="btn btn-success me-2">
                            <i class="bx bx-save me-1"></i> Simpan Surat
                        </button>
                        <a href="{{ route('surat-rekomendasi.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i> Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection