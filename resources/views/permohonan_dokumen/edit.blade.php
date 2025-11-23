@extends('layouts.app')

@section('title', 'Edit Dokumen Persyaratan')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Dokumen Persyaratan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('permohonan-dokumen.update', $permohonanDokumen) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label" for="permohonan_id">Permohonan</label>
                            <select class="form-select @error('permohonan_id') is-invalid @enderror" 
                                    id="permohonan_id" name="permohonan_id" disabled>
                                <option value="{{ $permohonanDokumen->permohonan->id }}">
                                    {{ $permohonanDokumen->permohonan->nomor_permohonan }} - {{ $permohonanDokumen->permohonan->nama_dokumen }}
                                </option>
                            </select>
                            @error('permohonan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="persyaratan_dokumen_id">Jenis Dokumen</label>
                            <select class="form-select @error('persyaratan_dokumen_id') is-invalid @enderror" 
                                    id="persyaratan_dokumen_id" name="persyaratan_dokumen_id" disabled>
                                <option value="{{ $permohonanDokumen->persyaratanDokumen->id }}">
                                    {{ $permohonanDokumen->persyaratanDokumen->nama }} 
                                    @if($permohonanDokumen->persyaratanDokumen->is_wajib)
                                        (Wajib)
                                    @else
                                        (Tidak Wajib)
                                    @endif
                                </option>
                            </select>
                            @error('persyaratan_dokumen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="is_ada">Status Dokumen</label>
                            <select class="form-select @error('is_ada') is-invalid @enderror" 
                                    id="is_ada" name="is_ada" required>
                                <option value="1" {{ old('is_ada', $permohonanDokumen->is_ada) == 1 ? 'selected' : '' }}>
                                    Dokumen Ada
                                </option>
                                <option value="0" {{ old('is_ada', $permohonanDokumen->is_ada) == 0 ? 'selected' : '' }}>
                                    Dokumen Tidak Ada
                                </option>
                            </select>
                            @error('is_ada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="file">File Dokumen</label>
                            @if($permohonanDokumen->file_path)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($permohonanDokumen->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-file me-1"></i> Lihat File Saat Ini
                                    </a>
                                    <small class="text-muted d-block mt-1">
                                        File: {{ $permohonanDokumen->file_name }} ({{ $permohonanDokumen->file_size_formatted }})
                                    </small>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">
                                Format: PDF, DOC, DOCX, JPG, PNG | Max: 10MB<br>
                                Kosongkan jika tidak ingin mengganti file
                            </div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Update</button>
                            <a href="{{ route('permohonan-dokumen.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#is_ada').change(function() {
        var isSelected = $(this).val() == '1';
        if(isSelected) {
            $('#file').prop('required', true);
        } else {
            $('#file').prop('required', false);
        }
    });

    $('#is_ada').trigger('change');
});
</script>
@endsection