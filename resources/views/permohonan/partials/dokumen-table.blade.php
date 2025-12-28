<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr class="text-center">
                <th width="40%">Nama Dokumen</th>
                <th width="10%">File</th>
                <th width="10%">Status Upload</th>
                <th width="11%">Status Verifikasi</th>
                <th width="19%">Catatan Verifikasi</th>
                @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                    <th width="10%">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($dokumenList as $dokumen)
                <tr>
                    <td>
                        <div>
                            <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen' }}</strong>
                        </div>
                        @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->deskripsi)
                            <small class="text-muted d-block mt-1">
                                <i class='bx bx-info-circle'></i>
                                {{ $dokumen->masterKelengkapan->deskripsi }}
                            </small>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($dokumen->file_path)
                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                                class="btn btn-xs btn-outline-primary"
                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                <i class="bx bx-download" style="font-size: 0.875rem;"></i> Lihat
                            </a>
                        @else
                            <span>-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($dokumen->is_ada)
                            <span class="badge bg-label-success" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                <i class='bx bx-check' style="font-size: 0.75rem;"></i> Tersedia
                            </span>
                        @else
                            <span class="badge bg-label-danger" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                <i class='bx bx-x' style="font-size: 0.75rem;"></i> Belum Upload
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($dokumen->status_verifikasi === 'verified')
                            <span class="badge bg-success" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                <i class='bx bx-check-circle' style="font-size: 0.75rem;"></i> Sesuai
                            </span>
                        @elseif($dokumen->status_verifikasi === 'revision')
                            <span class="badge bg-danger" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                <i class='bx bx-x-circle' style="font-size: 0.75rem;"></i> Revisi
                            </span>
                        @else
                            <span class="badge bg-secondary" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                <i class='bx bx-time' style="font-size: 0.75rem;"></i> Pending
                            </span>
                        @endif
                    </td>
                    <td>
                        @if ($dokumen->catatan_verifikasi)
                            <small class="text-{{ $dokumen->status_verifikasi === 'verified' ? 'success' : 'danger' }}">
                                <i
                                    class='bx bx-{{ $dokumen->status_verifikasi === 'verified' ? 'check-circle' : 'error-circle' }}'></i>
                                {{ $dokumen->catatan_verifikasi }}
                            </small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                        <td>
                            @if ($dokumen->status_verifikasi === 'verified')
                                <span class="badge bg-success">
                                    <i class='bx bx-lock'></i> Terverifikasi
                                </span>
                            @elseif ($dokumen->file_path && $dokumen->status_verifikasi !== 'revision')
                                <span class="badge bg-success">
                                    <i class='bx bx-check'></i> Selesai
                                </span>
                            @elseif ($permohonan->isUploadDeadlinePassed())
                                <span class="badge bg-danger">
                                    <i class='bx bx-lock'></i> Batas Waktu Terlewati
                                </span>
                            @else
                                <form action="{{ route('permohonan-dokumen.upload', $dokumen) }}" method="POST"
                                    enctype="multipart/form-data" class="upload-dokumen-form mb-0"
                                    data-dokumen-id="{{ $dokumen->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="file" name="file" class="file-input d-none"
                                        accept=".pdf,.xls,.xlsx" required>
                                    <button type="button"
                                        class="btn btn-sm btn-{{ $dokumen->status_verifikasi === 'revision' ? 'warning' : 'primary' }} btn-upload-trigger">
                                        <i class="bx bx-upload"></i>
                                        {{ $dokumen->status_verifikasi === 'revision' ? 'Upload Ulang' : 'Upload' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
