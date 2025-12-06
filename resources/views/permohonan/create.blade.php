@extends('layouts.app')

@section('title', 'Buat Permohonan Baru')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Buat Permohonan Baru</h5>
                        <p class="text-muted mb-0">Pilih jadwal fasilitasi untuk membuat permohonan</p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('permohonan.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="jadwal_fasilitasi_id">Jadwal Fasilitasi</label>
                                <select class="form-select @error('jadwal_fasilitasi_id') is-invalid @enderror"
                                    id="jadwal_fasilitasi_id" name="jadwal_fasilitasi_id" required>
                                    <option value="">Pilih Jadwal Fasilitasi</option>
                                    @foreach ($jadwalFasilitasi as $jadwal)
                                        <option value="{{ $jadwal->id }}"
                                            {{ old('jadwal_fasilitasi_id', $selectedJadwal?->id ?? null) == $jadwal->id ? 'selected' : '' }}>
                                            {{ strtoupper($jadwal->jenis_dokumen) }} {{ $jadwal->tahun_anggaran }} -
                                            Batas:
                                            {{ $jadwal->batas_permohonan ? $jadwal->batas_permohonan->format('d M Y') : '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    @if ($jadwalFasilitasi->isEmpty())
                                        <span class="text-danger">Tidak ada jadwal fasilitasi yang aktif saat ini.</span>
                                    @else
                                        Hanya jadwal yang aktif dan belum melewati batas permohonan yang bisa dipilih.
                                    @endif
                                </div>
                                @error('jadwal_fasilitasi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($selectedJadwal)
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Info Jadwal:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Jenis Dokumen:</strong> {{ strtoupper($selectedJadwal->jenis_dokumen) }}
                                        </li>
                                        <li><strong>Tahun Anggaran:</strong> {{ $selectedJadwal->tahun_anggaran }}</li>
                                        <li><strong>Periode:</strong> {{ $selectedJadwal->tanggal_mulai->format('d M Y') }}
                                            - {{ $selectedJadwal->tanggal_selesai->format('d M Y') }}</li>
                                        <li><strong>Batas Permohonan:</strong>
                                            {{ $selectedJadwal->batas_permohonan ? $selectedJadwal->batas_permohonan->format('d M Y') : '-' }}
                                        </li>
                                    </ul>
                                </div>
                            @endif

                            <div class="alert alert-warning">
                                <i class='bx bx-info-circle me-1'></i>
                                <strong>Perhatian:</strong> Setelah permohonan dibuat, Anda akan diminta untuk melengkapi
                                dokumen persyaratan.
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary me-2"
                                    {{ $jadwalFasilitasi->isEmpty() ? 'disabled' : '' }}>
                                    <i class='bx bx-plus me-1'></i> Buat Permohonan
                                </button>
                                <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
