{{--
    Alur dokumen hasil fasilitasi dalam tiga langkah: draft dokumen, draft final, ajukan ke Kaban.
    Hanya untuk Admin dan Koordinator. Variabel: $permohonan, $hasilFasilitasi, $isAdmin, $isKoordinator.
--}}
@php
    $jumlahMasukan = $hasilFasilitasi->hasilSistematika->count() + $hasilFasilitasi->hasilUrusan->count()
        + $hasilFasilitasi->hasilForm->count() + $hasilFasilitasi->hasilRekomendasi->count();
    $formatTanggal = fn ($tanggal) => $tanggal?->locale('id')->translatedFormat('d M Y, H:i');
    $langkah = [
        1 => (bool) $hasilFasilitasi->draft_file,
        2 => (bool) $hasilFasilitasi->draft_final_file,
        3 => $hasilFasilitasi->status_draft === \App\Enums\StatusHasilFasilitasi::DisetujuiKaban,
    ];
    $penandaLangkah = fn (int $nomor) => $langkah[$nomor]
        ? '<span class="badge rounded-pill bg-success me-2"><i class="bx bx-check"></i></span>'
        : '<span class="badge rounded-pill bg-label-secondary me-2">'.$nomor.'</span>';
@endphp

<div class="card mb-4" id="dokumen-hasil">
    <div class="card-header pb-0">
        <h5 class="mb-0"><i class="bx bx-file"></i> Dokumen Hasil Fasilitasi</h5>
    </div>
    <div class="card-body pt-3">
        <div class="row g-4">
            {{-- Langkah 1: Draft dokumen --}}
            <div class="col-lg-4">
                <h6 class="d-flex align-items-center mb-2">{!! $penandaLangkah(1) !!} Draft Dokumen</h6>
                @if ($hasilFasilitasi->draft_file)
                    <p class="small text-muted mb-2">Draft dibuat dari masukan yang ada. Buat ulang setelah ada perubahan masukan.</p>
                @elseif ($isKoordinator)
                    <p class="small text-muted mb-2">
                        {{ $jumlahMasukan > 0 ? 'Buat draft dari seluruh masukan yang sudah diinput.' : 'Tambahkan masukan terlebih dahulu sebelum membuat draft.' }}
                    </p>
                @else
                    <p class="small text-muted mb-2">Menunggu koordinator membuat draft dokumen.</p>
                @endif
                <div class="d-flex flex-wrap gap-2">
                    @if ($isKoordinator && $jumlahMasukan > 0)
                        <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}"
                            class="btn btn-sm {{ $hasilFasilitasi->draft_file ? 'btn-outline-warning' : 'btn-primary' }}">
                            <i class="bx {{ $hasilFasilitasi->draft_file ? 'bx-refresh' : 'bx-file-blank' }}"></i>
                            {{ $hasilFasilitasi->draft_file ? 'Buat Ulang Draft' : 'Buat Draft Dokumen' }}
                        </a>
                    @endif
                    @if ($hasilFasilitasi->draft_file)
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#previewPdfModal">
                            <i class="bx bx-show"></i> Lihat
                        </button>
                        <a href="{{ route('hasil-fasilitasi.download-word', $permohonan->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-download"></i> Word
                        </a>
                        <a href="{{ route('hasil-fasilitasi.download-pdf', $permohonan->id) }}" class="btn btn-sm btn-outline-danger">
                            <i class="bx bx-download"></i> PDF
                        </a>
                    @endif
                </div>
            </div>

            {{-- Langkah 2: Draft final (PDF dengan kop surat) --}}
            <div class="col-lg-4 border-lg-start">
                <h6 class="d-flex align-items-center mb-2">{!! $penandaLangkah(2) !!} Draft Final</h6>
                @if (! $hasilFasilitasi->draft_file)
                    <p class="small text-muted mb-0">Tersedia setelah draft dokumen dibuat.</p>
                @else
                    <p class="small text-muted mb-2">PDF final yang sudah dilengkapi kop surat dan lampiran.</p>
                    @if ($hasilFasilitasi->draft_final_file)
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <a href="{{ route('hasil-fasilitasi.download-draft-final', $permohonan->id) }}" class="btn btn-sm btn-success">
                                <i class="bx bx-download"></i> Unduh Draft Final
                            </a>
                            @unless ($hasilFasilitasi->isLocked())
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse"
                                    data-bs-target="#formUploadDraftFinal">
                                    <i class="bx bx-refresh"></i> Upload Ulang
                                </button>
                            @endunless
                        </div>
                    @endif
                    @unless ($hasilFasilitasi->isLocked())
                        <form id="formUploadDraftFinal"
                            action="{{ route('hasil-fasilitasi.upload-draft-final', $permohonan->id) }}" method="POST"
                            enctype="multipart/form-data"
                            class="collapse {{ $hasilFasilitasi->draft_final_file && ! $errors->has('draft_final_file') ? '' : 'show' }}">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="file" class="form-control @error('draft_final_file') is-invalid @enderror"
                                    id="draft_final_file" name="draft_final_file" accept=".pdf" required
                                    aria-label="Upload draft final PDF">
                                <button type="submit" class="btn btn-primary"><i class="bx bx-upload"></i> Upload</button>
                            </div>
                            @error('draft_final_file')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format PDF, maksimal 100MB.</div>
                        </form>
                    @endunless
                @endif
            </div>

            {{-- Langkah 3: Ajukan ke Kepala Badan --}}
            <div class="col-lg-4 border-lg-start">
                <h6 class="d-flex align-items-center mb-2">{!! $penandaLangkah(3) !!} Persetujuan Kepala Badan</h6>
                @if ($hasilFasilitasi->status_draft === \App\Enums\StatusHasilFasilitasi::MenungguPersetujuanKaban)
                    <p class="small mb-0">
                        <span class="badge bg-label-warning"><i class="bx bx-time"></i> Menunggu persetujuan</span><br>
                        <span class="text-muted">Diajukan {{ $formatTanggal($hasilFasilitasi->tanggal_diajukan_kaban) ?? '-' }}</span>
                    </p>
                @elseif ($hasilFasilitasi->status_draft === \App\Enums\StatusHasilFasilitasi::DisetujuiKaban)
                    <p class="small mb-0">
                        <span class="badge bg-label-success"><i class="bx bx-check-circle"></i> Sudah disetujui</span><br>
                        <span class="text-muted">{{ $formatTanggal($hasilFasilitasi->tanggal_disetujui_kaban) ?? '-' }}</span>
                    </p>
                @elseif (! $hasilFasilitasi->draft_final_file)
                    <p class="small text-muted mb-0">Tersedia setelah draft final diupload.</p>
                @elseif ($isAdmin)
                    <p class="small text-muted mb-2">
                        @if ($hasilFasilitasi->status_draft === \App\Enums\StatusHasilFasilitasi::DitolakKaban)
                            Dikembalikan Kepala Badan. Perbaiki masukan dan draft final, lalu ajukan kembali.
                        @else
                            Draft final siap diajukan untuk persetujuan.
                        @endif
                    </p>
                    <form action="{{ route('hasil-fasilitasi.submit-to-kaban', $permohonan->id) }}" method="POST"
                        class="js-konfirmasi" data-konfirmasi="Ajukan dokumen ini ke Kepala Badan untuk persetujuan? Masukan akan terkunci selama dinilai.">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bx bx-send"></i> Ajukan ke Kaban
                        </button>
                    </form>
                @else
                    <p class="small text-muted mb-0">Draft final siap; Admin akan mengajukan ke Kepala Badan.</p>
                @endif
            </div>
        </div>
    </div>
</div>
