@if (!$permohonan->tindakLanjut)
    <div class="alert alert-info">
        <i class='bx bx-info-circle me-2'></i>
        <strong>Tindak Lanjut:</strong> Form upload dokumen perencanaan yang telah diperbaiki akan tersedia setelah
        hasil fasilitasi divalidasi.
    </div>
@else
    <!-- Informasi Tindak Lanjut -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class='bx bx-task me-2'></i>Tindak Lanjut - Upload Dokumen Perbaikan
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-3">
                <i class='bx bx-info-circle me-1'></i>
                Silakan upload dokumen perencanaan yang telah diperbaiki berdasarkan masukan dan catatan penyempurnaan
                dari hasil fasilitasi.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Jenis Tindak Lanjut:</th>
                            <td>
                                @php
                                    $jenisLabels = [
                                        'perbaikan' => ['text' => 'Perbaikan', 'class' => 'warning'],
                                        'revisi_total' => ['text' => 'Revisi Total', 'class' => 'danger'],
                                        'sudah_sesuai' => ['text' => 'Sudah Sesuai', 'class' => 'success'],
                                    ];
                                    $current = $jenisLabels[$permohonan->tindakLanjut->jenis_tindak_lanjut] ?? [
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
                            <th>Batas Waktu:</th>
                            <td>
                                @if ($permohonan->tindakLanjut->batas_waktu)
                                    <strong
                                        class="text-{{ now()->gt($permohonan->tindakLanjut->batas_waktu) ? 'danger' : 'primary' }}">
                                        {{ \Carbon\Carbon::parse($permohonan->tindakLanjut->batas_waktu)->format('d F Y') }}
                                    </strong>
                                    @if (now()->gt($permohonan->tindakLanjut->batas_waktu))
                                        <br><span class="badge bg-danger mt-1">Sudah Lewat</span>
                                    @else
                                        <br><span class="badge bg-success mt-1">
                                            {{ now()->diffInDays($permohonan->tindakLanjut->batas_waktu) }} hari lagi
                                        </span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status Upload:</th>
                            <td>
                                @if ($permohonan->tindakLanjut->file_tindak_lanjut)
                                    <span class="badge bg-success">
                                        <i class='bx bx-check'></i> Sudah Diupload
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class='bx bx-time'></i> Belum Diupload
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if ($permohonan->tindakLanjut->keterangan)
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-2"><i class='bx bx-note me-1'></i>Keterangan:</h6>
                                <p class="mb-0">{{ $permohonan->tindakLanjut->keterangan }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Dokumen Tindak Lanjut -->
    @if (auth()->user()->hasRole('pemohon') && $permohonan->tindakLanjut->jenis_tindak_lanjut !== 'sudah_sesuai')
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-upload me-2'></i>Upload Dokumen Tindak Lanjut
                </h5>
            </div>
            <div class="card-body">
                @if ($permohonan->tindakLanjut->file_tindak_lanjut)
                    <div class="alert alert-success">
                        <i class='bx bx-check-circle me-2'></i>
                        Dokumen tindak lanjut sudah diupload
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <strong>File yang diupload:</strong><br>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($permohonan->tindakLanjut->tanggal_upload)->format('d F Y, H:i') }}
                                WIB
                            </small>
                        </div>
                        <div>
                            <a href="{{ asset('storage/' . $permohonan->tindakLanjut->file_tindak_lanjut) }}"
                                target="_blank" class="btn btn-primary me-2">
                                <i class='bx bx-download'></i> Download
                            </a>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#uploadTindakLanjutModal">
                                <i class='bx bx-upload'></i> Upload Ulang
                            </button>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class='bx bx-error me-2'></i>
                        Silakan upload dokumen tindak lanjut sesuai dengan hasil fasilitasi.
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#uploadTindakLanjutModal">
                        <i class='bx bx-upload'></i> Upload Dokumen
                    </button>
                @endif
            </div>
        </div>
    @elseif ($permohonan->tindakLanjut->file_tindak_lanjut)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-file me-2'></i>Dokumen Tindak Lanjut
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <strong>File Tindak Lanjut</strong><br>
                        <small class="text-muted">
                            Diupload:
                            {{ \Carbon\Carbon::parse($permohonan->tindakLanjut->tanggal_upload)->format('d F Y, H:i') }}
                            WIB
                        </small>
                    </div>
                    <div>
                        <a href="{{ asset('storage/' . $permohonan->tindakLanjut->file_tindak_lanjut) }}"
                            target="_blank" class="btn btn-primary">
                            <i class='bx bx-download'></i> Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Catatan Admin -->
    @if ($permohonan->tindakLanjut->catatan_admin)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-message-square-detail me-2'></i>Catatan Admin
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    {{ $permohonan->tindakLanjut->catatan_admin }}
                </div>
                @if ($permohonan->tindakLanjut->admin)
                    <small class="text-muted">
                        Oleh: {{ $permohonan->tindakLanjut->admin->name }} -
                        {{ \Carbon\Carbon::parse($permohonan->tindakLanjut->updated_at)->format('d F Y, H:i') }} WIB
                    </small>
                @endif
            </div>
        </div>
    @endif
@endif

<!-- Modal Upload Tindak Lanjut -->
@if (auth()->user()->hasRole('pemohon') &&
        $permohonan->tindakLanjut &&
        $permohonan->tindakLanjut->jenis_tindak_lanjut !== 'sudah_sesuai')
    <div class="modal fade" id="uploadTindakLanjutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Dokumen Tindak Lanjut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tindak-lanjut.upload', $permohonan->tindakLanjut) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">File Dokumen (PDF, max 10MB)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf" required>
                            <small class="text-muted">Upload dokumen yang telah diperbaiki sesuai hasil
                                fasilitasi</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan (Opsional)</label>
                            <textarea name="keterangan_upload" class="form-control" rows="3"
                                placeholder="Jelaskan perubahan yang telah dilakukan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-upload'></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
