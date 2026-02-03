<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Informasi Permohonan -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class='bx bx-info-circle me-2'></i>Informasi Permohonan</h5>
                @if ($permohonan->status_akhir == 'belum' && auth()->user()->hasRole('pemohon'))
                    <a href="{{ route('permohonan.edit', $permohonan) }}" class="btn btn-sm btn-primary">
                        <i class='bx bx-edit'></i> Edit
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Kabupaten/Kota</strong></div>
                    <div class="col-sm-8">{{ $permohonan->kabupatenKota->nama ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Tahun Anggaran</strong></div>
                    <div class="col-sm-8">{{ $permohonan->tahun }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Jenis Dokumen</strong></div>
                    <div class="col-sm-8">
                        <span class="badge bg-label-primary">
                            {{ strtoupper($permohonan->jenisDokumen->nama ?? $permohonan->jenis_dokumen) }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Status</strong></div>
                    <div class="col-sm-8">
                        <span class="badge bg-{{ $permohonan->status_badge_class }}">
                            {{ $permohonan->status_label }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Jadwal Fasilitasi</strong></div>
                    <div class="col-sm-8">
                        @if ($permohonan->jadwalFasilitasi)
                            <strong>{{ $permohonan->jadwalFasilitasi->tanggal_mulai->format('d M Y') }} -
                                {{ $permohonan->jadwalFasilitasi->tanggal_selesai->format('d M Y') }}</strong><br>
                            <small class="text-muted">
                                Batas Upload:
                                {{ $permohonan->jadwalFasilitasi->batas_permohonan ? $permohonan->jadwalFasilitasi->batas_permohonan->format('d M Y') : '-' }}
                            </small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Tanggal Dibuat</strong></div>
                    <div class="col-sm-8">{{ $permohonan->created_at->format('d M Y H:i') }}</div>
                </div>
                @if ($permohonan->submitted_at)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Tanggal Submit</strong></div>
                        <div class="col-sm-8">{{ $permohonan->submitted_at->format('d M Y H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        @if ($permohonan->status_akhir == 'belum' && auth()->user()->hasRole('pemohon'))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-task me-2'></i>Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    @php
                        $dokumenBelumLengkap = $permohonan->permohonanDokumen->where('is_ada', false)->count();
                        $totalDokumen = $permohonan->permohonanDokumen->count();
                        $dokumenTerlengkapi = $totalDokumen - $dokumenBelumLengkap;
                    @endphp

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Kelengkapan Dokumen</small>
                            <small
                                class="text-muted"><strong>{{ $dokumenTerlengkapi }}/{{ $totalDokumen }}</strong></small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $dokumenBelumLengkap == 0 ? 'bg-success' : 'bg-warning' }}"
                                role="progressbar"
                                style="width: {{ $totalDokumen > 0 ? ($dokumenTerlengkapi / $totalDokumen) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit"
                            class="btn btn-success w-100 {{ $dokumenBelumLengkap > 0 ? 'disabled' : '' }}"
                            {{ $dokumenBelumLengkap > 0 ? 'disabled' : '' }}>
                            <i class='bx bx-send me-1'></i> Submit Permohonan
                        </button>
                    </form>

                    @if ($dokumenBelumLengkap > 0)
                        <div class="alert alert-warning mb-0">
                            <i class='bx bx-info-circle me-2'></i>
                            <small>Masih ada {{ $dokumenBelumLengkap }} dokumen yang belum diupload.
                                Silakan lengkapi di tab <strong>Dokumen</strong>.</small>
                        </div>
                    @endif

                    <hr>

                    <form action="{{ route('permohonan.destroy', $permohonan) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class='bx bx-trash me-1'></i> Hapus Permohonan
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column - Timeline -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class='bx bx-time me-2'></i>Timeline Aktivitas</h5>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    <li class="timeline-item timeline-item-transparent">
                        <span class="timeline-point timeline-point-primary"></span>
                        <div class="timeline-event">
                            <div class="timeline-header mb-1">
                                <h6 class="mb-0">Permohonan Dibuat</h6>
                                <small class="text-muted">{{ $permohonan->created_at->format('d M Y H:i') }}</small>
                            </div>
                            <p class="mb-0 text-muted">Permohonan berhasil dibuat</p>
                        </div>
                    </li>

                    @if ($permohonan->submitted_at)
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Permohonan Disubmit</h6>
                                    <small
                                        class="text-muted">{{ $permohonan->submitted_at->format('d M Y H:i') }}</small>
                                </div>
                                <p class="mb-0 text-muted">Permohonan masuk tahap verifikasi</p>
                            </div>
                        </li>
                    @endif

                    @if ($permohonan->undanganPelaksanaan)
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-info"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Undangan Diterbitkan</h6>
                                    <small
                                        class="text-muted">{{ $permohonan->undanganPelaksanaan->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <p class="mb-0 text-muted">Undangan pelaksanaan fasilitasi diterbitkan</p>
                            </div>
                        </li>
                    @endif

                    @if ($permohonan->hasilFasilitasi)
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-warning"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Hasil Fasilitasi</h6>
                                    <small
                                        class="text-muted">{{ $permohonan->hasilFasilitasi->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <p class="mb-0 text-muted">Hasil fasilitasi telah diinput</p>
                            </div>
                        </li>
                    @endif

                    @if ($permohonan->penetapanPerda)
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">PERDA/PERKADA Ditetapkan</h6>
                                    <small
                                        class="text-muted">{{ $permohonan->penetapanPerda->tanggal_penetapan->format('d M Y') }}</small>
                                </div>
                                <p class="mb-0 text-muted">{{ $permohonan->penetapanPerda->nomor_penetapan }}</p>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
