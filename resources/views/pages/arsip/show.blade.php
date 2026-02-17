@extends('layouts.app')

@use('Illuminate\Support\Facades\Storage')

@section('title', 'Detail Arsip Dokumen')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Arsip Dokumen</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('arsip.index') }}">Arsip Dokumen</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <!-- Permohonan Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Kabupaten/Kota</p>
                        <h5 class="mb-3">{{ $permohonan->kabupatenKota->nama ?? 'N/A' }}</h5>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Jenis Dokumen</p>
                        <h6 class="mb-3">{{ $permohonan->jenisDokumen->nama_dokumen ?? 'N/A' }}</h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted small mb-1">Tahun</p>
                        <h6 class="mb-3">{{ $permohonan->tahun }}</h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted small mb-1">Tahapan Saat Ini</p>
                        @if($permohonan->tahapanAktif && $permohonan->tahapanAktif->masterTahapan)
                            <span class="badge bg-label-info">
                                {{ $permohonan->tahapanAktif->masterTahapan->nama_tahapan }}
                            </span>
                        @else
                            <span class="badge bg-label-secondary">Belum Dimulai</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted small mb-1">Status Akhir</p>
                        @if($permohonan->status_akhir == 'selesai')
                            <span class="badge bg-success">Selesai</span>
                        @elseif($permohonan->status_akhir == 'ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                        @else
                            <span class="badge bg-warning">Proses</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            
            <!-- 1. Dokumen Permohonan -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['dokumen_permohonan'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-file"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['dokumen_permohonan'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['dokumen_permohonan'] }} File
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Dokumen Permohonan</h5>
                        <p class="text-muted small mb-3">Dokumen awal yang diajukan pemohon</p>
                        
                        @if($documentCounts['dokumen_permohonan'] > 0)
                            <div class="list-group list-group-flush mb-3">
                                @foreach(($permohonan->permohonanDokumen ?? collect())->take(3) as $dok)
                                    <div class="list-group-item px-0 py-2">
                                        <small class="d-flex justify-content-between">
                                            <span>
                                                <i class="bx bx-file-blank me-1"></i>
                                                @if($dok->masterKelengkapan)
                                                    {{ str()->limit($dok->masterKelengkapan->nama_dokumen, 30) }}
                                                @else
                                                    {{ str()->limit($dok->file_name ?? 'Dokumen', 30) }}
                                                @endif
                                            </span>
                                            @if($dok->file_path)
                                                <a href="{{ Storage::url($dok->file_path) }}" target="_blank" class="text-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            @endif
                                        </small>
                                    </div>
                                @endforeach
                                @if($permohonan->permohonanDokumen && $permohonan->permohonanDokumen->count() > 3)
                                    <small class="text-muted">+{{ $permohonan->permohonanDokumen->count() - 3 }} lainnya</small>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada dokumen</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Dokumen Tahapan -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['dokumen_tahapan'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-layer"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['dokumen_tahapan'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['dokumen_tahapan'] }} File
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Dokumen Tahapan</h5>
                        <p class="text-muted small mb-3">Dokumen yang diupload selama proses</p>
                        
                        @if($documentCounts['dokumen_tahapan'] > 0)
                            <div class="list-group list-group-flush mb-3">
                                @foreach(($permohonan->dokumenTahapan ?? collect())->take(3) as $dok)
                                    <div class="list-group-item px-0 py-2">
                                        <small class="d-flex justify-content-between">
                                            <span>
                                                <i class="bx bx-file-blank me-1"></i>
                                                {{ str()->limit($dok->nama_file ?? 'Dokumen Tahapan', 30) }}
                                            </span>
                                            @if($dok->file_path)
                                                <a href="{{ Storage::url($dok->file_path) }}" target="_blank" class="text-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            @endif
                                        </small>
                                    </div>
                                @endforeach
                                @if($permohonan->dokumenTahapan && $permohonan->dokumenTahapan->count() > 3)
                                    <small class="text-muted">+{{ $permohonan->dokumenTahapan->count() - 3 }} lainnya</small>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada dokumen</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. Laporan Verifikasi -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['laporan_verifikasi'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-check-shield"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['laporan_verifikasi'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['laporan_verifikasi'] > 0 ? '1 Dokumen' : '0 Dokumen' }}
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Laporan Verifikasi</h5>
                        <p class="text-muted small mb-3">Hasil verifikasi dokumen</p>
                        
                        @if($permohonan->laporanVerifikasi)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Status:</small>
                                <span class="badge bg-label-success mb-2">Sudah Diverifikasi</span>
                                @if($permohonan->laporanVerifikasi->file_laporan)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($permohonan->laporanVerifikasi->file_laporan) }}" 
                                           target="_blank" class="text-success small">
                                            <i class="bx bx-download me-1"></i>Download Laporan
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada laporan</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. Hasil Fasilitasi -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['hasil_fasilitasi'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-edit"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['hasil_fasilitasi'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['hasil_fasilitasi'] > 0 ? '1 Dokumen' : '0 Dokumen' }}
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Hasil Fasilitasi</h5>
                        <p class="text-muted small mb-3">Dokumen hasil evaluasi tim</p>
                        
                        @if($permohonan->hasilFasilitasi)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Status:</small>
                                @if($permohonan->hasilFasilitasi->status_draft == 'disetujui_kaban')
                                    <span class="badge bg-success mb-2">Disetujui Kepala Badan</span>
                                @elseif($permohonan->hasilFasilitasi->status_draft == 'submitted_to_kaban')
                                    <span class="badge bg-info mb-2">Menunggu Persetujuan</span>
                                @elseif($permohonan->hasilFasilitasi->status_draft == 'submitted')
                                    <span class="badge bg-warning mb-2">Diajukan</span>
                                @else
                                    <span class="badge bg-label-secondary mb-2">Draft</span>
                                @endif
                                @if($permohonan->hasilFasilitasi->draft_file_final)
                                    <div class="mt-2">
                                        <a href="{{ route('hasil-fasilitasi.download-draft-final', $permohonan) }}" 
                                           target="_blank" class="text-success small">
                                            <i class="bx bx-download me-1"></i>Download Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('hasil-fasilitasi.show', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada hasil fasilitasi</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 5. Jadwal & Undangan -->
            <div class="col">
                <div class="card h-100 {{ ($documentCounts['jadwal_fasilitasi'] + $documentCounts['undangan_pelaksanaan']) > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-calendar"></i>
                                </span>
                            </div>
                            <span class="badge {{ ($documentCounts['jadwal_fasilitasi'] + $documentCounts['undangan_pelaksanaan']) > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['jadwal_fasilitasi'] + $documentCounts['undangan_pelaksanaan'] }} Dokumen
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Jadwal & Undangan</h5>
                        <p class="text-muted small mb-3">Jadwal dan undangan pelaksanaan</p>
                        
                        @if($documentCounts['jadwal_fasilitasi'] > 0 || $documentCounts['undangan_pelaksanaan'] > 0)
                            <div class="mb-3">
                                @if($documentCounts['jadwal_fasilitasi'] > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Jadwal Fasilitasi: </small>
                                        <span class="badge bg-label-primary">1</span>
                                        @if($permohonan->jadwalFasilitasi)
                                            <div class="mt-2">
                                                <a href="{{ route('jadwal.show', $permohonan->jadwalFasilitasi) }}" 
                                                   class="text-success small">
                                                    <i class="bx bx-show me-1"></i>Lihat Jadwal
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if($documentCounts['undangan_pelaksanaan'] > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Undangan: </small>
                                        <span class="badge bg-label-primary">{{ $documentCounts['undangan_pelaksanaan'] }}</span>
                                        @if($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->isNotEmpty())
                                            <div class="mt-2">
                                                <a href="{{ route('undangan-pelaksanaan.show', $permohonan) }}" 
                                                   class="text-success small">
                                                    <i class="bx bx-show me-1"></i>Lihat Undangan
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada jadwal/undangan</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 6. Surat-Surat Resmi -->
            <div class="col">
                <div class="card h-100 {{ ($documentCounts['surat_pemberitahuan'] + $documentCounts['surat_rekomendasi'] + $documentCounts['surat_penyampaian_hasil']) > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-envelope"></i>
                                </span>
                            </div>
                            <span class="badge {{ ($documentCounts['surat_pemberitahuan'] + $documentCounts['surat_rekomendasi'] + $documentCounts['surat_penyampaian_hasil']) > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['surat_pemberitahuan'] + $documentCounts['surat_rekomendasi'] + $documentCounts['surat_penyampaian_hasil'] }} Surat
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Surat-Surat Resmi</h5>
                        <p class="text-muted small mb-3">Surat Pemberitahuan, Rekomendasi, dll</p>
                        
                        @if($documentCounts['surat_pemberitahuan'] > 0 || $documentCounts['surat_rekomendasi'] > 0 || $documentCounts['surat_penyampaian_hasil'] > 0)
                            <div class="mb-3">
                                @if($documentCounts['surat_pemberitahuan'] > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Surat Pemberitahuan: </small>
                                        <span class="badge bg-label-primary">{{ $documentCounts['surat_pemberitahuan'] }}</span>
                                    </div>
                                @endif
                                @if($documentCounts['surat_rekomendasi'] > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Surat Rekomendasi: </small>
                                        <span class="badge bg-label-primary">1</span>
                                    </div>
                                @endif
                                @if($documentCounts['surat_penyampaian_hasil'] > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Surat Penyampaian Hasil: </small>
                                        <span class="badge bg-label-primary">1</span>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada surat</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 7. Perpanjangan Waktu -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['perpanjangan_waktu'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time-five"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['perpanjangan_waktu'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['perpanjangan_waktu'] }} Pengajuan
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Perpanjangan Waktu</h5>
                        <p class="text-muted small mb-3">Pengajuan perpanjangan waktu</p>
                        
                        @if($documentCounts['perpanjangan_waktu'] > 0)
                            <div class="list-group list-group-flush mb-3">
                                @foreach(($permohonan->perpanjanganWaktu ?? collect())->take(2) as $perpanjangan)
                                    <div class="list-group-item px-0 py-2">
                                        <small>
                                            <span class="badge bg-label-{{ $perpanjangan->status == 'disetujui' ? 'success' : ($perpanjangan->status == 'ditolak' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($perpanjangan->status) }}
                                            </span>
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Tidak ada perpanjangan</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 8. Tindak Lanjut -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['tindak_lanjut'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-task"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['tindak_lanjut'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['tindak_lanjut'] > 0 ? '1 Dokumen' : '0 Dokumen' }}
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Tindak Lanjut</h5>
                        <p class="text-muted small mb-3">Dokumen tindak lanjut fasilitasi</p>
                        
                        @if($permohonan->tindakLanjut)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Status:</small>
                                @if($permohonan->tindakLanjut->status == 'submitted')
                                    <span class="badge bg-success mb-2">Sudah Disubmit</span>
                                @else
                                    <span class="badge bg-label-secondary mb-2">Draft</span>
                                @endif
                                @if($permohonan->tindakLanjut->file_path)
                                    <div class="mt-2">
                                        <a href="{{ route('tindak-lanjut.download', $permohonan) }}" 
                                           target="_blank" class="text-success small">
                                            <i class="bx bx-download me-1"></i>Download Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada tindak lanjut</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 9. Penetapan Perda -->
            <div class="col">
                <div class="card h-100 {{ $documentCounts['penetapan_perda'] > 0 ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                            </div>
                            <span class="badge {{ $documentCounts['penetapan_perda'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $documentCounts['penetapan_perda'] > 0 ? '1 Dokumen' : '0 Dokumen' }}
                            </span>
                        </div>
                        <h5 class="card-title mb-2">Penetapan Perda</h5>
                        <p class="text-muted small mb-3">Dokumen penetapan akhir</p>
                        
                        @if($permohonan->penetapanPerda)
                            <div class="mb-3">
                                @if($permohonan->penetapanPerda->nomor_penetapan)
                                    <small class="text-muted d-block mb-1">Nomor Penetapan:</small>
                                    <span class="badge bg-label-success mb-2">{{ $permohonan->penetapanPerda->nomor_penetapan }}</span>
                                @else
                                    <span class="badge bg-label-info mb-2">Penetapan tersedia</span>
                                @endif
                                @if($permohonan->penetapanPerda->file_penetapan)
                                    <div class="mt-2">
                                        <a href="{{ route('penetapan-perda.download', $permohonan) }}" 
                                           target="_blank" class="text-success small">
                                            <i class="bx bx-download me-1"></i>Download Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('permohonan.show-tabs', $permohonan) }}" class="btn btn-sm btn-success w-100">
                                <i class="bx bx-show me-1"></i> Lihat Detail
                            </a>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>Belum ada penetapan</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
