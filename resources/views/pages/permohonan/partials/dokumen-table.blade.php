<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr class="text-center">
                @if (auth()->user()->hasRole('verifikator') && $permohonan->status_akhir == 'proses')
                    <th width="5%" class="text-center">No</th>
                    <th width="28%">Nama Dokumen</th>
                    <th width="10%" class="text-center">File</th>
                    <th width="10%" class="text-center">Status</th>
                    <th width="15%">Verifikasi</th>
                    <th width="22%">Catatan</th>
                    <th width="10%" class="text-center">Aksi</th>
                @else
                    <th width="40%">Nama Dokumen</th>
                    <th width="10%">File</th>
                    <th width="10%">Status Upload</th>
                    <th width="11%">Status Verifikasi</th>
                    <th width="19%">Catatan Verifikasi</th>
                    @if ($permohonan->status_akhir == 'belum' || $permohonan->status_akhir == 'revisi')
                        <th width="10%">Aksi</th>
                    @endif
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($dokumenList as $index => $dokumen)
                <tr>
                    @if (auth()->user()->hasRole('verifikator') && $permohonan->status_akhir == 'proses')
                        {{-- Tampilan untuk Verifikator --}}
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div>
                                <strong>{{ $dokumen->masterKelengkapan->nama_dokumen ?? 'Dokumen' }}</strong>
                                @if ($dokumen->masterKelengkapan && $dokumen->masterKelengkapan->wajib)
                                    <span class="badge badge-sm bg-label-danger ms-1">Wajib</span>
                                @endif
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
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-download"></i> Lihat
                                </a>
                            @else
                                <span class="badge bg-label-danger">Belum upload</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($dokumen->is_ada)
                                <span class="badge bg-label-success"><i class='bx bx-check'></i> Ada</span>
                            @else
                                <span class="badge bg-label-danger"><i class='bx bx-x'></i> Tidak</span>
                            @endif
                        </td>
                        <td>
                            <select class="form-select form-select-sm verifikasi-status"
                                data-dokumen-id="{{ $dokumen->id }}"
                                {{ $dokumen->status_verifikasi === 'verified' ? 'disabled' : '' }}>
                                <option value="pending"
                                    {{ $dokumen->status_verifikasi === 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="verified"
                                    {{ $dokumen->status_verifikasi === 'verified' ? 'selected' : '' }}>
                                    ✓ Sesuai</option>
                                <option value="revision"
                                    {{ $dokumen->status_verifikasi === 'revision' ? 'selected' : '' }}>
                                    ✗ Revisi</option>
                            </select>
                            @if ($dokumen->status_verifikasi === 'revision' && $dokumen->file_path)
                                <small class="text-info d-block mt-1">
                                    <i class='bx bx-info-circle'></i> Dokumen telah diupload ulang
                                </small>
                            @endif
                        </td>
                        <td>
                            <textarea class="form-control form-control-sm catatan-verifikasi" data-dokumen-id="{{ $dokumen->id }}" rows="2"
                                placeholder="Catatan..." {{ $dokumen->status_verifikasi === 'verified' ? 'disabled' : '' }}>{{ $dokumen->catatan_verifikasi }}</textarea>
                        </td>
                        <td class="text-center">
                            @if ($dokumen->status_verifikasi === 'verified')
                                <span class="badge bg-success"><i class='bx bx-check-circle'></i> Selesai</span>
                            @else
                                <button type="button" class="btn btn-sm btn-primary btn-verifikasi-dokumen-table"
                                    data-dokumen-id="{{ $dokumen->id }}">
                                    <i class='bx bx-save'></i>
                                    {{ $dokumen->status_verifikasi === 'revision' ? 'Verifikasi Ulang' : 'Simpan' }}
                                </button>
                            @endif
                        </td>
                    @else
                        {{-- Tampilan untuk Non-Verifikator (Pemohon, Admin, dll) --}}
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
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-download"></i> Lihat
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($dokumen->is_ada)
                                <span class="badge bg-label-success">
                                    <i class='bx bx-check'></i> Tersedia
                                </span>
                            @else
                                <span class="badge bg-label-danger">
                                    <i class='bx bx-x'></i> Belum Upload
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
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
                                    <i class='bx bx-time'></i> Pending
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
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
