@if (!$permohonan->penetapanPerda)
    <div class="alert alert-info">
        <i class='bx bx-info-circle me-2'></i>
        <strong>Penetapan PERDA/PERKADA:</strong> Form upload PERDA/PERKADA yang telah ditetapkan akan tersedia setelah
        tindak lanjut selesai dan dokumen final disetujui.
    </div>

    @if (auth()->user()->hasRole('pemohon') && $permohonan->tindakLanjut)
        <!-- Form Upload PERDA/PERKADA untuk Pemohon -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class='bx bx-upload me-2'></i>Upload PERDA/PERKADA yang Telah Ditetapkan
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-primary">
                    <i class='bx bx-info-circle me-1'></i>
                    Silakan upload dokumen PERDA/PERKADA yang telah ditetapkan oleh pemerintah daerah Anda.
                </div>

                <form action="{{ route('permohonan.penetapan.store', $permohonan->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomor_penetapan" class="form-label">Nomor Penetapan <span
                                        class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('nomor_penetapan') is-invalid @enderror"
                                    id="nomor_penetapan" name="nomor_penetapan" required
                                    placeholder="Contoh: Nomor 1 Tahun 2025">
                                @error('nomor_penetapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_penetapan" class="form-label">Tanggal Penetapan <span
                                        class="text-danger">*</span></label>
                                <input type="date"
                                    class="form-control @error('tanggal_penetapan') is-invalid @enderror"
                                    id="tanggal_penetapan" name="tanggal_penetapan" required>
                                @error('tanggal_penetapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_dokumen" class="form-label">Jenis Dokumen <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('jenis_dokumen') is-invalid @enderror" id="jenis_dokumen"
                            name="jenis_dokumen" required>
                            <option value="">Pilih Jenis Dokumen</option>
                            <option value="perda">PERDA (Peraturan Daerah)</option>
                            <option value="perkada">PERKADA (Peraturan Kepala Daerah)</option>
                        </select>
                        @error('jenis_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="file_perda" class="form-label">Upload File PERDA/PERKADA <span
                                class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('file_perda') is-invalid @enderror"
                            id="file_perda" name="file_perda" accept=".pdf" required>
                        <div class="form-text">Format: PDF. Maksimal 10MB.</div>
                        @error('file_perda')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                            rows="3" placeholder="Tambahkan keterangan jika diperlukan"></textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-upload me-1'></i>Upload PERDA/PERKADA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@else
    <!-- Informasi Penetapan PERDA -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class='bx bx-certification me-2'></i>Penetapan PERDA/PERKADA
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success mb-3">
                <i class='bx bx-check-circle me-1'></i>
                PERDA/PERKADA telah diupload oleh pemohon dan tercatat dalam sistem.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Nomor Penetapan:</th>
                            <td><strong>{{ $permohonan->penetapanPerda->nomor_penetapan ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Penetapan:</th>
                            <td>
                                @if ($permohonan->penetapanPerda->tanggal_penetapan)
                                    <strong>{{ \Carbon\Carbon::parse($permohonan->penetapanPerda->tanggal_penetapan)->format('d F Y') }}</strong>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Jenis Dokumen:</th>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $permohonan->penetapanPerda->jenis_dokumen == 'perda' ? 'PERDA' : 'PERATURAN DAERAH' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status Publikasi:</th>
                            <td>
                                @if ($permohonan->penetapanPerda->is_published)
                                    <span class="badge bg-success">
                                        <i class='bx bx-globe'></i> Sudah Dipublikasi
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class='bx bx-lock'></i> Belum Dipublikasi
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-2"><i class='bx bx-info-circle me-1'></i>Tentang PERDA:</h6>
                            <p class="mb-2"><strong>{{ $permohonan->penetapanPerda->tentang ?? '-' }}</strong></p>
                            @if ($permohonan->penetapanPerda->keterangan)
                                <hr>
                                <small class="text-muted">{{ $permohonan->penetapanPerda->keterangan }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($permohonan->penetapanPerda->tanggal_publikasi)
                <div class="alert alert-success mt-3">
                    <i class='bx bx-calendar-check me-2'></i>
                    <strong>Tanggal Publikasi:</strong>
                    {{ \Carbon\Carbon::parse($permohonan->penetapanPerda->tanggal_publikasi)->format('d F Y') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Dokumen Penetapan -->
    @if ($permohonan->penetapanPerda->file_penetapan)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-file-blank me-2'></i>Dokumen Penetapan
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class='bx bx-file-blank bx-lg text-primary'></i>
                    <p class="mb-1 mt-2"><strong>Dokumen Resmi Penetapan PERDA</strong></p>
                    <small class="text-muted">Format: PDF</small>
                </div>
                <a href="{{ asset('storage/' . $permohonan->penetapanPerda->file_penetapan) }}" target="_blank"
                    class="btn btn-primary btn-lg">
                    <i class='bx bx-download'></i> Download Dokumen Penetapan
                </a>
            </div>
        </div>
    @endif

    <!-- Timeline Proses -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class='bx bx-time-five me-2'></i>Timeline Proses
            </h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon bg-primary">
                        <i class='bx bx-send'></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Permohonan Diajukan</h6>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($permohonan->created_at)->format('d F Y, H:i') }} WIB
                        </small>
                    </div>
                </div>

                @if ($permohonan->laporanVerifikasi)
                    <div class="timeline-item">
                        <div class="timeline-icon bg-info">
                            <i class='bx bx-check-shield'></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Verifikasi Selesai</h6>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($permohonan->laporanVerifikasi->tanggal_verifikasi)->format('d F Y') }}
                            </small>
                        </div>
                    </div>
                @endif

                @if ($permohonan->jadwalFasilitasi)
                    <div class="timeline-item">
                        <div class="timeline-icon bg-warning">
                            <i class='bx bx-calendar-event'></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Jadwal Fasilitasi Ditetapkan</h6>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->tanggal_mulai)->format('d F Y') }}
                            </small>
                        </div>
                    </div>
                @endif

                @if ($permohonan->hasilFasilitasi)
                    <div class="timeline-item">
                        <div class="timeline-icon bg-success">
                            <i class='bx bx-check-double'></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Pelaksanaan Fasilitasi</h6>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($permohonan->hasilFasilitasi->tanggal_pelaksanaan)->format('d F Y') }}
                            </small>
                        </div>
                    </div>
                @endif

                <div class="timeline-item">
                    <div class="timeline-icon bg-success">
                        <i class='bx bx-certification'></i>
                    </div>
                    <div class="timeline-content">
                        <h6>PERDA Ditetapkan</h6>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($permohonan->penetapanPerda->tanggal_penetapan)->format('d F Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-icon {
        position: absolute;
        left: -40px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
    }

    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
    }
</style>
