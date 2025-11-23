@extends('layouts.app')

@section('title', 'Buat Permohonan Baru')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Buat Permohonan Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('permohonan.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label" for="tahun_anggaran_id">Tahun Anggaran</label>
                            <select class="form-select @error('tahun_anggaran_id') is-invalid @enderror" 
                                    id="tahun_anggaran_id" name="tahun_anggaran_id" required>
                                <option value="">Pilih Tahun Anggaran</option>
                                @foreach($tahunAnggaran as $tahun)
                                    <option value="{{ $tahun->id }}" {{ old('tahun_anggaran_id') == $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->tahun }} - {{ $tahun->nama ?? 'Tahun ' . $tahun->tahun }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_anggaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="jenis_dokumen_id">Jenis Dokumen</label>
                            <select class="form-select @error('jenis_dokumen_id') is-invalid @enderror" 
                                    id="jenis_dokumen_id" name="jenis_dokumen_id" required>
                                <option value="">Pilih Jenis Dokumen</option>
                                @foreach($jenisDokumen as $dokumen)
                                    <option value="{{ $dokumen->id }}" {{ old('jenis_dokumen_id') == $dokumen->id ? 'selected' : '' }}>
                                        {{ $dokumen->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_dokumen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="jadwal_fasilitasi_id">Jadwal Fasilitasi</label>
                            <select class="form-select @error('jadwal_fasilitasi_id') is-invalid @enderror" 
                                    id="jadwal_fasilitasi_id" name="jadwal_fasilitasi_id" required>
                                <option value="">Pilih Jadwal Fasilitasi</option>
                                @foreach($jadwalFasilitasi as $jadwal)
                                    <option value="{{ $jadwal->id }}" 
                                            data-jenis-dokumen="{{ $jadwal->jenisDokumen->id }}"
                                            {{ old('jadwal_fasilitasi_id') == $jadwal->id ? 'selected' : '' }}>
                                        {{ $jadwal->nama_kegiatan }} ({{ $jadwal->tahunAnggaran->tahun ?? '-' }}) - 
                                        Batas: {{ $jadwal->batas_permohonan->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">
                                Hanya jadwal yang aktif dan belum melewati batas permohonan yang bisa dipilih.
                            </div>
                            @error('jadwal_fasilitasi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="nama_dokumen">Nama Dokumen</label>
                            <input type="text" class="form-control @error('nama_dokumen') is-invalid @enderror" 
                                   id="nama_dokumen" name="nama_dokumen" value="{{ old('nama_dokumen') }}" required>
                            @error('nama_dokumen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="tanggal_permohonan">Tanggal Permohonan</label>
                            <input type="date" class="form-control @error('tanggal_permohonan') is-invalid @enderror" 
                                   id="tanggal_permohonan" name="tanggal_permohonan" 
                                   value="{{ old('tanggal_permohonan', now()->format('Y-m-d')) }}" required>
                            @error('tanggal_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="keterangan">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" name="keterangan" rows="3">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Simpan & Lanjut</button>
                            <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">Batal</a>
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
        $('#tahun_anggaran_id, #jenis_dokumen_id, #jadwal_fasilitasi_id').select2({
            placeholder: "Pilih...",
            allowClear: true
        });

        // Filter jadwal berdasarkan jenis dokumen yang dipilih
        $('#jenis_dokumen_id').change(function() {
            let selectedJenisDokumen = $(this).val();
            
            $('#jadwal_fasilitasi_id option').each(function() {
                let optionJenisDokumen = $(this).data('jenis-dokumen');
                
                if (selectedJenisDokumen && optionJenisDokumen != selectedJenisDokumen) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });
    });
</script>
@endsection