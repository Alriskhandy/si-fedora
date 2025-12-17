@extends('layouts.app')

@section('title', 'Tambah Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Jadwal Fasilitasi Baru</h5>
                        <p class="text-muted mb-0">Buat jadwal global untuk Kab/Kota mengajukan permohonan</p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('jadwal.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="tahun_anggaran">Tahun Anggaran</label>
                                <input type="number" min="2000" max="2100"
                                    class="form-control @error('tahun_anggaran') is-invalid @enderror" id="tahun_anggaran"
                                    name="tahun_anggaran" value="{{ old('tahun_anggaran', date('Y')) }}" required>
                                @error('tahun_anggaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="jenis_dokumen">Jenis Dokumen</label>
                                <select class="form-select @error('jenis_dokumen') is-invalid @enderror" id="jenis_dokumen"
                                    name="jenis_dokumen" required>
                                    <option value="">Pilih Jenis Dokumen</option>
                                    @foreach ($jenisdokumen as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('jenis_dokumen') == $item->nama ? 'selected' : '' }}>
                                            {{ strtoupper($item->nama) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_dokumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tanggal_mulai">Tanggal Mulai Fasilitasi</label>
                                <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                    id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tanggal_selesai">Tanggal Selesai Fasilitasi</label>
                                <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                    id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                    required>
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="batas_permohonan">Batas Waktu Permohonan</label>
                                <input type="date" class="form-control @error('batas_permohonan') is-invalid @enderror"
                                    id="batas_permohonan" name="batas_permohonan" value="{{ old('batas_permohonan') }}">
                                @error('batas_permohonan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Deadline untuk Kab/Kota mengajukan permohonan</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="undangan_file">Surat Penyampaian Jadwal Verifikasi (PDF)</label>
                                <input type="file" class="form-control @error('undangan_file') is-invalid @enderror"
                                    id="undangan_file" name="undangan_file" accept=".pdf">
                                @error('undangan_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: PDF, Maksimal 5MB</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>
                                        Published</option>
                                    <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Hanya jadwal "Published" yang dapat dilihat Kab/Kota</small>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                                <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
