@extends('layouts.app')

@section('title', 'Tambah Jadwal Fasilitasi')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Jadwal Fasilitasi Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('jadwal.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="permohonan_id">Permohonan</label>
                                <select class="form-select @error('permohonan_id') is-invalid @enderror" id="permohonan_id"
                                    name="permohonan_id" required>
                                    <option value="">Pilih Permohonan</option>
                                    @foreach ($permohonan as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('permohonan_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->kabupatenKota?->nama ?? '-' }} - {{ strtoupper($p->jenis_dokumen) }}
                                            {{ $p->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('permohonan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pilih permohonan yang belum memiliki jadwal</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tanggal_pelaksanaan">Tanggal Pelaksanaan</label>
                                <input type="date"
                                    class="form-control @error('tanggal_pelaksanaan') is-invalid @enderror"
                                    id="tanggal_pelaksanaan" name="tanggal_pelaksanaan"
                                    value="{{ old('tanggal_pelaksanaan') }}" required>
                                @error('tanggal_pelaksanaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tempat">Tempat</label>
                                <input type="text" class="form-control @error('tempat') is-invalid @enderror"
                                    id="tempat" name="tempat" value="{{ old('tempat') }}"
                                    placeholder="Contoh: Ruang Rapat Lantai 3" required>
                                @error('tempat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="undangan_file">File Undangan (PDF)</label>
                                <input type="file" class="form-control @error('undangan_file') is-invalid @enderror"
                                    id="undangan_file" name="undangan_file" accept=".pdf">
                                @error('undangan_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: PDF, Maksimal 2MB</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="keterangan">Keterangan</label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                                    rows="3" placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#permohonan_id').select2({
                placeholder: "Pilih Permohonan",
                allowClear: true
            });
        });
    </script>
@endsection
