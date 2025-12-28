@if (!$permohonan->jadwalFasilitasi)
    <div class="alert alert-info">
        <i class='bx bx-info-circle me-2'></i>
        <strong>Penetapan Jadwal:</strong> Jadwal fasilitasi akan ditetapkan oleh Kepala Badan (Kaban) setelah verifikasi dokumen selesai.
    </div>
@else
    <!-- Informasi Jadwal -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class='bx bx-calendar-event me-2'></i>Penetapan Jadwal Pelaksanaan Fasilitasi
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class='bx bx-info-circle me-1'></i>
                Jadwal ini telah ditetapkan oleh Kepala Badan (Kaban).
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Tanggal Pelaksanaan:</th>
                            <td>
                                <strong>{{ $permohonan->jadwalFasilitasi->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->tanggal_mulai)->format('d F Y') : '-' }}</strong>
                                @if ($permohonan->jadwalFasilitasi->tanggal_selesai)
                                    <br><small class="text-muted">s/d
                                        {{ \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->tanggal_selesai)->format('d F Y') }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Waktu:</th>
                            <td>{{ $permohonan->jadwalFasilitasi->waktu ?? '-' }} WIB</td>
                        </tr>
                        <tr>
                            <th>Tempat:</th>
                            <td>{{ $permohonan->jadwalFasilitasi->tempat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Dokumen:</th>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $permohonan->jadwalFasilitasi->jenis_dokumen == 'raperda' ? 'RAPERDA' : 'RANPERDA' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Batas Upload Dokumen:</th>
                            <td>
                                <strong class="text-{{ $permohonan->isUploadDeadlinePassed() ? 'danger' : 'warning' }}">
                                    {{ $permohonan->jadwalFasilitasi->batas_permohonan ? \Carbon\Carbon::parse($permohonan->jadwalFasilitasi->batas_permohonan)->format('d F Y, H:i') : '-' }}
                                    WIB
                                </strong>
                                @if ($permohonan->isUploadDeadlinePassed())
                                    <br><span class="badge bg-danger mt-1">Sudah Lewat</span>
                                @else
                                    <br><span class="badge bg-success mt-1">Masih Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Koordinator:</th>
                            <td>{{ $permohonan->jadwalFasilitasi->koordinatorAssignment->koordinator->name ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penetapan:</th>
                            <td>
                                @if ($permohonan->jadwalFasilitasi->status_penetapan)
                                    <span class="badge bg-success"><i class='bx bx-check'></i> Sudah Ditetapkan</span>
                                @else
                                    <span class="badge bg-warning"><i class='bx bx-time'></i> Belum Ditetapkan</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if ($permohonan->jadwalFasilitasi->catatan)
                <div class="alert alert-info mt-3">
                    <strong><i class='bx bx-note me-1'></i>Catatan:</strong><br>
                    {{ $permohonan->jadwalFasilitasi->catatan }}
                </div>
            @endif
        </div>
    </div>

    <!-- Undangan Pelaksanaan -->
    @if ($permohonan->undanganPelaksanaan && $permohonan->undanganPelaksanaan->file_undangan)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-envelope me-2'></i>Undangan Pelaksanaan
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2">
                            <strong>Nomor Surat:</strong> {{ $permohonan->undanganPelaksanaan->nomor_surat ?? '-' }}
                        </p>
                        <p class="mb-0">
                            <strong>Tanggal Surat:</strong>
                            {{ $permohonan->undanganPelaksanaan->tanggal_surat ? \Carbon\Carbon::parse($permohonan->undanganPelaksanaan->tanggal_surat)->format('d F Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ asset('storage/' . $permohonan->undanganPelaksanaan->file_undangan) }}"
                            target="_blank" class="btn btn-primary">
                            <i class='bx bx-download'></i> Download Undangan
                        </a>
                    </div>
                </div>

                <!-- Konfirmasi Kehadiran -->
                @if (auth()->user()->hasRole('pemohon'))
                    @if ($permohonan->undanganPelaksanaan->konfirmasi_kehadiran === null)
                        <div class="alert alert-warning mt-3">
                            <i class='bx bx-bell me-2'></i>
                            Mohon konfirmasi kehadiran Anda untuk pelaksanaan fasilitasi.
                        </div>
                        <div class="mt-3">
                            <form
                                action="{{ route('undangan-pelaksanaan.konfirmasi', $permohonan->undanganPelaksanaan) }}"
                                method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="konfirmasi_kehadiran" value="1">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class='bx bx-check'></i> Konfirmasi Hadir
                                </button>
                            </form>
                            <form
                                action="{{ route('undangan-pelaksanaan.konfirmasi', $permohonan->undanganPelaksanaan) }}"
                                method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="konfirmasi_kehadiran" value="0">
                                <button type="submit" class="btn btn-danger">
                                    <i class='bx bx-x'></i> Tidak Hadir
                                </button>
                            </form>
                        </div>
                    @else
                        <div
                            class="alert alert-{{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'success' : 'danger' }} mt-3">
                            <i
                                class='bx bx-{{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'check-circle' : 'x-circle' }} me-2'></i>
                            <strong>Konfirmasi Kehadiran:</strong>
                            {{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'Hadir' : 'Tidak Hadir' }}
                            @if ($permohonan->undanganPelaksanaan->keterangan_kehadiran)
                                <br>{{ $permohonan->undanganPelaksanaan->keterangan_kehadiran }}
                            @endif
                        </div>
                    @endif
                @elseif ($permohonan->undanganPelaksanaan->konfirmasi_kehadiran !== null)
                    <div
                        class="alert alert-{{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'success' : 'danger' }} mt-3">
                        <i
                            class='bx bx-{{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'check-circle' : 'x-circle' }} me-2'></i>
                        <strong>Status Kehadiran Pemohon:</strong>
                        {{ $permohonan->undanganPelaksanaan->konfirmasi_kehadiran ? 'Hadir' : 'Tidak Hadir' }}
                        @if ($permohonan->undanganPelaksanaan->keterangan_kehadiran)
                            <br>{{ $permohonan->undanganPelaksanaan->keterangan_kehadiran }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
