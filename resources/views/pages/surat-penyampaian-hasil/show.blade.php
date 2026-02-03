@extends('layouts.app')

@section('title', 'Detail Surat Penyampaian Hasil')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Surat Penyampaian Hasil /</span> Detail
        </h4>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Surat Penyampaian Hasil</h5>
                        @if ($hasilFasilitasi->surat_penyampaian)
                            <a href="{{ route('public.surat-penyampaian-hasil.download', $permohonan) }}"
                                class="btn btn-primary btn-sm">
                                <i class="bx bx-download"></i> Download Surat
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Kabupaten/Kota:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $permohonan->kabupatenKota->nama ?? '-' }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Jenis Dokumen:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-label-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Tahun:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $permohonan->tahun }}
                            </div>
                        </div>

                        @if ($hasilFasilitasi)
                            <hr>

                            @if ($hasilFasilitasi->surat_penyampaian)
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Nomor Surat:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        {{ $hasilFasilitasi->nomor_surat ?? '-' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Tanggal Surat:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        {{ $hasilFasilitasi->surat_tanggal ? $hasilFasilitasi->surat_tanggal->format('d F Y') : '-' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Dibuat Oleh:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        {{ $hasilFasilitasi->suratDibuatOleh->name ?? '-' }}
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle"></i> Surat penyampaian hasil belum diterbitkan
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Status Akhir:</strong><br>
                            <span class="badge bg-label-{{ $permohonan->status_badge_class }} mt-1">
                                {{ $permohonan->status_label }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <strong>Tanggal Dibuat:</strong><br>
                            <small class="text-muted">{{ $permohonan->created_at->format('d F Y H:i') }}</small>
                        </div>

                        @if ($permohonan->updated_at != $permohonan->created_at)
                            <div class="mb-3">
                                <strong>Terakhir Update:</strong><br>
                                <small class="text-muted">{{ $permohonan->updated_at->format('d F Y H:i') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
