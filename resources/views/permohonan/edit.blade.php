@extends('layouts.app')

@section('title', 'Edit Permohonan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Informasi Permohonan (Tidak Bisa Diubah) -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Informasi Permohonan</h6>
                            <div class="mb-2">
                                <strong>Jenis Dokumen:</strong>
                                <span class="badge bg-primary ms-2">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Tahun:</strong> {{ $permohonan->tahun }}
                            </div>
                            <div class="mb-2">
                                <strong>Jadwal Fasilitasi:</strong>
                                {{ $permohonan->jadwalFasilitasi->tanggal_mulai->format('d M Y') }} s/d
                                {{ $permohonan->jadwalFasilitasi->tanggal_selesai->format('d M Y') }}
                            </div>
                            <hr>
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Data permohonan tidak dapat diubah. Untuk melengkapi dokumen persyaratan,
                                silakan klik "Lihat Detail" di bawah ini.
                            </small>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('permohonan.show', $permohonan) }}" class="btn btn-primary">
                                <i class="bx bx-show me-1"></i> Lihat Detail & Kelola Dokumen
                            </a>
                            <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
                            </a>
                        </div>

                        <!-- Submit Button -->
                        @if ($permohonan->status_akhir == 'belum')
                            <div class="mt-4 pt-4 border-top">
                                <h6>Submit Permohonan</h6>
                                <p class="text-muted">Pastikan semua dokumen persyaratan telah dilengkapi sebelum mengirim
                                    permohonan ini.</p>
                                <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Yakin ingin mengirim permohonan ini? Setelah dikirim, permohonan akan masuk ke proses verifikasi dan tidak bisa diedit lagi.')">
                                        <i class="bx bx-send me-1"></i> Submit Permohonan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
