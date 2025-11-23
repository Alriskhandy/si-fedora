@extends('layouts.app')

@section('title', 'Upload Dokumen Persyaratan')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload Dokumen Persyaratan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('permohonan-dokumen.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label" for="permohonan_id">Permohonan</label>
                            <select class="form-select @error('permohonan_id') is-invalid @enderror" 
                                    id="permohonan_id" name="permohonan_id" required>
                                <option value="">Pilih Permohonan</option>
                                @foreach($permohonanList as $permohonan)
                                    <option value="{{ $permohonan->id }}" 
                                            {{ old('permohonan_id') == $permohonan->id ? 'selected' : '' }}>
                                        {{ $permohonan->nomor_permohonan }} - {{ $permohonan->nama_dokumen }}
                                    </option>
                                @endforeach
                            </select>
                            @error('permohonan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="persyaratan_dokumen_id">Jenis Dokumen</label>
                            <select class="form-select @error('persyaratan_dokumen_id') is-invalid @enderror" 
                                    id="persyaratan_dokumen_id" name="persyaratan_dokumen_id" required>
                                <option value="">Pilih Jenis Dokumen</option>
                                @foreach($persyaratanDokumen as $dokumen)
                                    <option value="{{ $dokumen->id }}" 
                                            data-is-wajib="{{ $dokumen->is_wajib ? 'true' : 'false' }}"
                                            {{ old('persyaratan_dokumen_id') == $dokumen->id ? 'selected' : '' }}>
                                        {{ $dokumen->nama }} 
                                        @if($dokumen->is_wajib)
                                            (Wajib)
                                        @else
                                            (Tidak Wajib)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('persyaratan_dokumen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="is_ada">Status Dokumen</label>
                            <select class="form-select @error('is_ada') is-invalid @enderror" 
                                    id="is_ada" name="is_ada" required>
                                <option value="1">Dokumen Ada</option>
                                <option value="0">Dokumen Tidak Ada</option>
                            </select>
                            @error('is_ada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="fileUploadSection" style="display: none;">
                            <label class="form-label" for="file">File Dokumen</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <div class="form-text">
                                Format: PDF, DOC, DOCX, JPG, PNG | Max: 10MB
                            </div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Simpan</button>
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
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
$(document).ready(function() {
    $('#permohonan_id, #persyaratan_dokumen_id').select2({
        placeholder: "Pilih...",
        allowClear: true
    });

    $('#is_ada').change(function() {
        var isSelected = $(this).val() == '1';
        if(isSelected) {
            $('#fileUploadSection').show();
        } else {
            $('#fileUploadSection').hide();
            $('#file').val('');
        }
    });

    // Trigger change on load
    $('#is_ada').trigger('change');
});
</script>
@endsection