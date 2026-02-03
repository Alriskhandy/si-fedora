@php
    $perpanjanganList = $permohonan->perpanjanganWaktu ?? collect();
@endphp
<div class="card h-100">
    <div class="card-header bg-primary ">
        <h5 class="mb-0 text-white">
            <i class='bx bx-time-five me-2'></i>Permohonan Perpanjangan Waktu
        </h5>
    </div>
    <div class="card-body">
        {{-- Status --}}
        @if ($permohonan->jadwalFasilitasi)
            @php
                $batasPermohonan = $permohonan->jadwalFasilitasi->batas_permohonan;
                $isDeadlinePassed = $batasPermohonan && now()->gt($batasPermohonan);
            @endphp
            <div class="alert {{ $isDeadlinePassed ? 'alert-danger' : 'alert-info' }} my-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class='bx {{ $isDeadlinePassed ? 'bx-error-circle' : 'bx-info-circle' }} me-2'></i>
                        <strong>Batas Upload Dokumen:</strong>
                        {{ \Carbon\Carbon::parse($batasPermohonan)->format('d F Y, H:i') }} WIB
                        @if ($isDeadlinePassed)
                            <br><small class="text-muted">Terlewat {{ now()->diffForHumans($batasPermohonan, true) }}
                                yang lalu</small>
                        @endif
                    </div>
                    <div>
                        @if (!$isDeadlinePassed)
                            <span class="badge bg-success">
                                <i class='bx bx-time'></i> Tersisa {{ now()->diffInDays($batasPermohonan) }} hari
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class='bx bx-x-circle'></i> Sudah Lewat
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Riwayat Permohonan yang sudah diajukan --}}
        @if ($perpanjanganList->count() > 0)
            <h6 class="mb-2">
                <i class='bx bx-check-circle me-2'></i>Permohonan Perpanjangan yang Telah Diajukan
            </h6>
            @foreach ($perpanjanganList as $perpanjangan)
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Tanggal Pengajuan:</small>
                                <p class="mb-1">
                                    {{ \Carbon\Carbon::parse($perpanjangan->created_at)->format('d M Y, H:i') }}</p>

                                <small class="text-muted">Alasan:</small>
                                <p class="mb-1">{{ $perpanjangan->alasan }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Surat Permohonan:</small>
                                @if ($perpanjangan->surat_permohonan)
                                    <p class="mb-1">
                                        <a href="{{ asset('storage/' . $perpanjangan->surat_permohonan) }}"
                                            target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class='bx bx-file-blank'></i> Lihat Surat
                                        </a>
                                    </p>
                                @else
                                    <p class="mb-1"><span class="badge bg-secondary">Belum Upload</span></p>
                                @endif

                                @if ($perpanjangan->catatan_admin)
                                    <small class="text-muted">Catatan Admin:</small>
                                    <p class="mb-0 text-{{ $perpanjangan->diproses_at ? 'info' : 'muted' }}">
                                        {{ $perpanjangan->catatan_admin }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Form & Panduan --}}
        <div class="row mt-5">
            <!-- Kolom Kiri: Form Perpanjangan -->
            <div class="col-lg-6 mb-4">

                @if (auth()->user()->hasRole('pemohon'))
                    <!-- Form Upload Surat Permohonan -->

                    <h6 class="mb-3">
                        <i class='bx bx-upload me-2'></i>Upload Surat Permohonan
                    </h6>

                    <form action="{{ route('perpanjangan-waktu.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="permohonan_id" value="{{ $permohonan->id }}">

                        <div class="mb-3">
                            <label for="alasan" class="form-label">
                                Alasan Keterlambatan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('alasan') is-invalid @enderror" id="alasan" name="alasan" rows="4"
                                placeholder="Jelaskan alasan Anda memerlukan perpanjangan waktu upload dokumen (minimal 20 karakter)" required>{{ old('alasan') }}</textarea>
                            @error('alasan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 20 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label for="surat_permohonan" class="form-label">
                                Surat Permohonan Perpanjangan Waktu <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('surat_permohonan') is-invalid @enderror"
                                id="surat_permohonan" name="surat_permohonan" accept=".pdf" required>
                            @error('surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: PDF (Maks 2MB)</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-send me-1'></i> Kirim Permohonan
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- Panduan Perpanjangan Waktu -->
            <div class="col-lg-6 mb-4">
                <h6 class="mb-3">
                    <i class='bx bx-help-circle me-2'></i>Panduan Perpanjangan Waktu
                </h6>


                <h6>Prosedur Pengajuan:</h6>
                <ol class="mb-3">
                    <li>Isi form alasan keterlambatan dengan jelas</li>
                    <li>Upload surat permohonan resmi yang sudah ditandatangani (PDF)</li>
                    <li>Klik tombol "Kirim Permohonan"</li>
                    <li>Tunggu proses verifikasi dari admin</li>
                </ol>
            </div>
        </div>
    </div>
</div>
