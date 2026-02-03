@extends('layouts.app')

@section('title', 'Tambah Surat Pemberitahuan')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Surat Pemberitahuan Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('surat-pemberitahuan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label" for="jadwal_fasilitasi_id">Jadwal Fasilitasi</label>
                            <select class="form-select @error('jadwal_fasilitasi_id') is-invalid @enderror" 
                                    id="jadwal_fasilitasi_id" name="jadwal_fasilitasi_id" required>
                                <option value="">Pilih Jadwal Fasilitasi</option>
                                @foreach($jadwalFasilitasi as $jadwal)
                                    <option value="{{ $jadwal->id }}" {{ old('jadwal_fasilitasi_id') == $jadwal->id ? 'selected' : '' }}>
                                        {{ $jadwal->nama_kegiatan }} - {{ $jadwal->tahunAnggaran->tahun ?? '-' }} ({{ $jadwal->jenisDokumen->nama ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('jadwal_fasilitasi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="kabupaten_kota_id">Kabupaten/Kota</label>
                            <select class="form-select @error('kabupaten_kota_id') is-invalid @enderror" 
                                    id="kabupaten_kota_id" name="kabupaten_kota_id" required>
                                <option value="">Pilih Kabupaten/Kota</option>
                                @foreach($kabupatenKota as $kabupaten)
                                    <option value="{{ $kabupaten->id }}" {{ old('kabupaten_kota_id') == $kabupaten->id ? 'selected' : '' }}>
                                        {{ $kabupaten->getFullNameAttribute() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kabupaten_kota_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="nomor_surat">Nomor Surat</label>
                            <input type="text" class="form-control @error('nomor_surat') is-invalid @enderror" 
                                   id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}">
                            <div class="form-text">Contoh: 005/XXX/XI/2025</div>
                            @error('nomor_surat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="tanggal_surat">Tanggal Surat</label>
                            <input type="date" class="form-control @error('tanggal_surat') is-invalid @enderror" 
                                   id="tanggal_surat" name="tanggal_surat" value="{{ old('tanggal_surat', now()->format('Y-m-d')) }}" required>
                            @error('tanggal_surat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="perihal">Perihal</label>
                            <input type="text" class="form-control @error('perihal') is-invalid @enderror" 
                                   id="perihal" name="perihal" value="{{ old('perihal') }}" required>
                            @error('perihal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="isi_surat">Isi Surat</label>
                            <textarea class="form-control @error('isi_surat') is-invalid @enderror" 
                                      id="isi_surat" name="isi_surat" rows="5">{{ old('isi_surat') }}</textarea>
                            @error('isi_surat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="file_path">File Surat (PDF/DOC)</label>
                            <input type="file" class="form-control @error('file_path') is-invalid @enderror" 
                                   id="file_path" name="file_path" accept=".pdf,.doc,.docx">
                            <div class="form-text">Max 10MB. Format: PDF, DOC, DOCX</div>
                            @error('file_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Simpan</button>
                            <a href="{{ route('surat-pemberitahuan.index') }}" class="btn btn-secondary">Batal</a>
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
        $('#jadwal_fasilitasi_id, #kabupaten_kota_id').select2({
            placeholder: "Pilih...",
            allowClear: true
        });
    });
</script>
@endsection