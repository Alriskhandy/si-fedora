{{--
    Ringkasan permohonan di halaman input hasil fasilitasi: satu baris info, batas waktu, dan status.
    Variabel: $permohonan, $hasilFasilitasi, $tahapanPelaksanaan, $tahapanHasil, $isAdmin, $isKoordinator.
--}}
@php
    $formatTanggal = fn ($tanggal) => $tanggal?->locale('id')->translatedFormat('d M Y, H:i');
    [$statusLabel, $statusBadge] = $hasilFasilitasi->statusLabel();
    $deadline = $tahapanHasil?->deadline;
    $isOverdue = $deadline && now()->gt($deadline);
    $pelaksanaanSelesai = $tahapanPelaksanaan?->status === \App\Enums\StatusPermohonan::Selesai ? $tahapanPelaksanaan->tanggal_selesai_tampil : null;
@endphp

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-4">
            <div>
                <small class="text-muted d-block">Kabupaten/Kota</small>
                <span class="fw-semibold">{{ $permohonan->kabupatenKota->nama }}</span>
            </div>
            <div>
                <small class="text-muted d-block">Dokumen</small>
                <span class="badge bg-primary">{{ strtoupper($permohonan->jenisDokumen->nama) }}</span>
                <span class="ms-1">{{ $permohonan->tahun }}</span>
            </div>
            <div>
                <small class="text-muted d-block">Pelaksanaan</small>
                @if ($pelaksanaanSelesai)
                    <span>{{ $formatTanggal($pelaksanaanSelesai) }}</span>
                @else
                    <span class="text-muted">Belum selesai</span>
                @endif
            </div>
            @if ($deadline)
                <div>
                    <small class="text-muted d-block">Batas Waktu Input</small>
                    <span class="badge {{ $isOverdue ? 'bg-label-danger' : 'bg-label-warning' }}">
                        <i class="bx {{ $isOverdue ? 'bx-error-circle' : 'bx-time' }}"></i>
                        {{ $formatTanggal($deadline) }}{{ $isOverdue ? ' (Terlewat)' : '' }}
                    </span>
                </div>
            @endif
            <div>
                <small class="text-muted d-block">Status</small>
                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
            </div>

            @unless ($isAdmin || $isKoordinator)
                <div class="ms-md-auto">
                    @if ($hasilFasilitasi->draft_file)
                        <div class="btn-group btn-group-sm" role="group" aria-label="Draft dokumen">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#previewPdfModal">
                                <i class="bx bx-show"></i> Lihat Draft
                            </button>
                            <a href="{{ route('hasil-fasilitasi.download-word', $permohonan->id) }}" class="btn btn-outline-primary">
                                <i class="bx bx-download"></i> Word
                            </a>
                            <a href="{{ route('hasil-fasilitasi.download-pdf', $permohonan->id) }}" class="btn btn-outline-primary">
                                <i class="bx bx-download"></i> PDF
                            </a>
                        </div>
                    @else
                        <small class="text-muted"><i class="bx bx-info-circle"></i> Draft dokumen belum dibuat koordinator</small>
                    @endif
                </div>
            @endunless
        </div>
    </div>
</div>
