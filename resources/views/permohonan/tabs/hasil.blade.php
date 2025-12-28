@if (!$permohonan->hasilFasilitasi)
    <div class="alert alert-info">
        <i class='bx bx-info-circle me-2'></i>
        <strong>Hasil Fasilitasi:</strong> Dokumen hasil fasilitasi beserta masukan dan catatan penyempurnaan dari
        fasilitator akan tersedia setelah pelaksanaan fasilitasi selesai.
    </div>
@else
    <!-- Informasi Hasil Fasilitasi -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class='bx bx-check-double me-2'></i>Hasil Fasilitasi & Catatan Penyempurnaan
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success mb-3">
                <i class='bx bx-info-circle me-1'></i>
                Berikut adalah hasil fasilitasi yang telah diinput oleh fasilitator beserta masukan dan catatan
                penyempurnaan untuk dokumen perencanaan Anda.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Tanggal Pelaksanaan:</th>
                            <td>{{ $permohonan->hasilFasilitasi->tanggal_pelaksanaan ? \Carbon\Carbon::parse($permohonan->hasilFasilitasi->tanggal_pelaksanaan)->format('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Validasi:</th>
                            <td>
                                @php
                                    $statusLabels = [
                                        'draft' => ['text' => 'Draft', 'class' => 'secondary'],
                                        'belum_divalidasi' => ['text' => 'Belum Divalidasi', 'class' => 'warning'],
                                        'tervalidasi' => ['text' => 'Tervalidasi', 'class' => 'success'],
                                        'ditolak' => ['text' => 'Ditolak', 'class' => 'danger'],
                                    ];
                                    $current = $statusLabels[$permohonan->hasilFasilitasi->status_validasi] ?? [
                                        'text' => 'Unknown',
                                        'class' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $current['class'] }}">
                                    {{ $current['text'] }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Diinput Oleh:</th>
                            <td>{{ $permohonan->hasilFasilitasi->fasilitator->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if ($permohonan->hasilFasilitasi->catatan_kaban)
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-2"><i class='bx bx-note me-1'></i>Catatan Kepala Badan:</h6>
                                <p class="mb-0">{{ $permohonan->hasilFasilitasi->catatan_kaban }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Hasil Fasilitasi per Sistematika -->
    @if ($permohonan->hasilFasilitasi->hasilFasilitasiSistematika->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-list-ul me-2'></i>Hasil Fasilitasi per Sistematika
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionSistematika">
                    @foreach ($permohonan->hasilFasilitasi->hasilFasilitasiSistematika as $index => $sistematika)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                    aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                    aria-controls="collapse{{ $index }}">
                                    <strong>{{ $sistematika->masterBab->nama_bab ?? 'Sistematika' }}</strong>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}"
                                class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                aria-labelledby="heading{{ $index }}" data-bs-parent="#accordionSistematika">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <h6>Keterangan:</h6>
                                        <p>{{ $sistematika->keterangan ?? '-' }}</p>
                                    </div>
                                    @if ($sistematika->saran)
                                        <div class="alert alert-info">
                                            <strong><i class='bx bx-bulb me-1'></i>Saran:</strong><br>
                                            {{ $sistematika->saran }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Hasil Fasilitasi per Urusan -->
    @if ($permohonan->hasilFasilitasi->hasilFasilitasiUrusan->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-briefcase me-2'></i>Hasil Fasilitasi per Urusan
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Urusan</th>
                                <th width="35%">Keterangan</th>
                                <th width="30%">Saran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permohonan->hasilFasilitasi->hasilFasilitasiUrusan as $index => $urusan)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $urusan->masterUrusan->nama_urusan ?? '-' }}</td>
                                    <td>{{ $urusan->keterangan ?? '-' }}</td>
                                    <td>
                                        @if ($urusan->saran)
                                            <span class="text-info">
                                                <i class='bx bx-bulb'></i> {{ $urusan->saran }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Download Laporan -->
    @if ($permohonan->hasilFasilitasi->status_validasi === 'tervalidasi')
        <div class="card">
            <div class="card-body text-center">
                <h5 class="mb-3">
                    <i class='bx bx-file-blank me-2'></i>Dokumen Hasil Fasilitasi
                </h5>
                <p class="text-muted mb-3">
                    Download laporan hasil fasilitasi yang telah tervalidasi
                </p>
                <a href="{{ route('hasil-fasilitasi.download-pdf', $permohonan->hasilFasilitasi) }}"
                    class="btn btn-primary btn-lg" target="_blank">
                    <i class='bx bx-download'></i> Download Laporan PDF
                </a>
            </div>
        </div>
    @endif
@endif
