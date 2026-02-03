@if ($permohonan->status_akhir == 'belum')
    <div class="alert alert-info">
        <i class='bx bx-info-circle me-2'></i>
        Verifikasi akan dilakukan setelah Anda mengirimkan permohonan.
    </div>

    @if (auth()->user()->hasRole('pemohon') && $permohonan->jadwalFasilitasi)
        @php
            $dokumenBelumLengkap = $permohonan->permohonanDokumen->where('is_ada', false)->count() > 0;
            $batasWaktu = $permohonan->jadwalFasilitasi->batas_permohonan;
            $batasWaktuTerlewat = $batasWaktu ? now()->gt($batasWaktu) : false;
        @endphp

        @if ($dokumenBelumLengkap && $batasWaktuTerlewat)
            <div class="alert alert-danger d-flex justify-content-between align-items-center">
                <div>
                    <i class='bx bx-error-circle me-2'></i>
                    <strong>Batas Waktu Upload Terlewat!</strong><br>
                    <small>Batas upload: {{ \Carbon\Carbon::parse($batasWaktu)->format('d M Y, H:i') }} WIB</small><br>
                    <small class="text-muted">Dokumen yang belum lengkap: <strong>{{ $permohonan->permohonanDokumen->where('is_ada', false)->count() }}</strong></small>
                </div>
                <a href="{{ route('perpanjangan-waktu.create', ['permohonan_id' => $permohonan->id]) }}" 
                   class="btn btn-warning">
                    <i class='bx bx-time-five me-1'></i>Ajukan Perpanjangan Waktu
                </a>
            </div>
        @endif
    @endif
@elseif (in_array($permohonan->status_akhir, ['dikirim', 'proses']))
    <div class="alert alert-warning">
        <i class='bx bx-time-five me-2'></i>
        <strong>Sedang dalam Proses Verifikasi</strong><br>
        Permohonan Anda sedang diverifikasi oleh tim verifikator. Mohon menunggu.
    </div>
@endif

<!-- Informasi Verifikasi -->
@if ($permohonan->laporanVerifikasi)
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class='bx bx-check-shield me-2'></i>Hasil Verifikasi
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Status Verifikasi:</th>
                            <td>
                                @php
                                    $statusLabel = [
                                        'lengkap' => ['text' => 'Lengkap', 'class' => 'success'],
                                        'tidak_lengkap' => ['text' => 'Tidak Lengkap', 'class' => 'danger'],
                                        'perlu_revisi' => ['text' => 'Perlu Revisi', 'class' => 'warning'],
                                    ];
                                    $current = $statusLabel[$permohonan->laporanVerifikasi->status_kelengkapan] ?? [
                                        'text' => 'Unknown',
                                        'class' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $current['class'] }}">
                                    <i
                                        class='bx bx-{{ $current['class'] == 'success' ? 'check-circle' : 'x-circle' }}'></i>
                                    {{ $current['text'] }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Diverifikasi Oleh:</th>
                            <td>{{ $permohonan->laporanVerifikasi->verifikator->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Verifikasi:</th>
                            <td>{{ $permohonan->laporanVerifikasi->tanggal_verifikasi ? \Carbon\Carbon::parse($permohonan->laporanVerifikasi->tanggal_verifikasi)->format('d F Y') : '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-2"><i class='bx bx-note me-1'></i>Catatan Verifikator:</h6>
                            <p class="mb-0">{{ $permohonan->laporanVerifikasi->catatan ?? 'Tidak ada catatan' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($permohonan->laporanVerifikasi->file_laporan)
                <div class="mt-3">
                    <a href="{{ asset('storage/' . $permohonan->laporanVerifikasi->file_laporan) }}" target="_blank"
                        class="btn btn-primary">
                        <i class='bx bx-download'></i> Download Laporan Verifikasi
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Detail Verifikasi per Dokumen -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class='bx bx-list-check me-2'></i>Status Verifikasi Dokumen
        </h5>
    </div>
    <div class="card-body">
        @php
            $groupedDokumen = $permohonan->permohonanDokumen->groupBy(function ($dok) {
                return $dok->masterKelengkapan->kategori ?? 'other';
            });
        @endphp

        @foreach (['surat_permohonan' => 'Surat Permohonan', 'kelengkapan_verifikasi' => 'Kelengkapan Verifikasi'] as $kategori => $label)
            @if (isset($groupedDokumen[$kategori]) && $groupedDokumen[$kategori]->count() > 0)
                <h6 class="mt-3 mb-3">{{ $label }}</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Nama Dokumen</th>
                                <th width="15%">Status Upload</th>
                                <th width="15%">Status Verifikasi</th>
                                <th width="30%">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedDokumen[$kategori] as $index => $dokumen)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen' }}</strong>
                                    </td>
                                    <td>
                                        @if ($dokumen->is_ada && $dokumen->file_path)
                                            <span class="badge bg-success">
                                                <i class='bx bx-check'></i> Tersedia
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class='bx bx-x'></i> Belum Upload
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dokumen->status_verifikasi === 'verified')
                                            <span class="badge bg-success">
                                                <i class='bx bx-check-circle'></i> Sesuai
                                            </span>
                                        @elseif($dokumen->status_verifikasi === 'revision')
                                            <span class="badge bg-danger">
                                                <i class='bx bx-x-circle'></i> Revisi
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class='bx bx-time'></i> Belum Diverifikasi
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dokumen->catatan_verifikasi)
                                            <small
                                                class="text-{{ $dokumen->status_verifikasi === 'verified' ? 'success' : 'danger' }}">
                                                <i
                                                    class='bx bx-{{ $dokumen->status_verifikasi === 'verified' ? 'check-circle' : 'error-circle' }}'></i>
                                                {{ $dokumen->catatan_verifikasi }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach

        @if ($permohonan->permohonanDokumen->count() === 0)
            <div class="text-center text-muted py-4">
                <i class='bx bx-folder-open bx-lg mb-2 d-block'></i>
                Belum ada dokumen yang diupload
            </div>
        @endif
    </div>
</div>
