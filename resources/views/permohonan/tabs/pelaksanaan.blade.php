@if (!$permohonan->jadwalFasilitasi)
    <div class="alert alert-info">
        <i class='bx bx-info-circle me-2'></i>
        Tab ini akan aktif setelah jadwal fasilitasi ditetapkan oleh Kaban.
    </div>
@else
    <!-- Header Info -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class='bx bx-clipboard me-2'></i>Dokumen Pelaksanaan Fasilitasi Luring
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">
                <i class='bx bx-info-circle me-1'></i>
                Upload dokumen-dokumen yang berkaitan dengan pelaksanaan fasilitasi secara luring seperti:
                daftar hadir, dokumentasi foto, berita acara, dan dokumen pendukung lainnya.
            </p>
        </div>
    </div>

    <!-- Upload Dokumen Pelaksanaan -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class='bx bx-file me-1'></i>Kelengkapan Dokumen Pelaksanaan</h6>
                @if (auth()->user()->hasAnyRole(['fasilitator', 'admin_peran', 'superadmin']))
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#uploadDokumenPelaksanaanModal">
                        <i class='bx bx-plus-circle me-1'></i>Upload Dokumen
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @php
                // Get dokumen pelaksanaan
                // Ambil master tahapan pelaksanaan terlebih dahulu
                $masterTahapanPelaksanaan = \App\Models\MasterTahapan::where('nama_tahapan', 'Pelaksanaan')->first();

                $dokumenPelaksanaan = collect();
                if ($masterTahapanPelaksanaan) {
                    $dokumenPelaksanaan = $permohonan
                        ->dokumenTahapan()
                        ->where('tahapan_id', $masterTahapanPelaksanaan->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
            @endphp

            @if ($dokumenPelaksanaan && $dokumenPelaksanaan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Jenis Dokumen</th>
                                <th width="20%">Nama File</th>
                                <th width="15%">Diupload Oleh</th>
                                <th width="15%">Tanggal Upload</th>
                                <th width="10%">Ukuran</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dokumenPelaksanaan as $index => $dokumen)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $dokumen->jenis_dokumen ?? 'Dokumen Pelaksanaan' }}</strong>
                                    </td>
                                    <td>
                                        <i class='bx bx-file-blank me-1'></i>
                                        {{ $dokumen->nama_file ?? basename($dokumen->file_path) }}
                                    </td>
                                    <td>
                                        <small>{{ $dokumen->uploadedBy->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $dokumen->created_at ? $dokumen->created_at->format('d M Y, H:i') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            @if ($dokumen->file_path && Storage::exists($dokumen->file_path))
                                                {{ round(Storage::size($dokumen->file_path) / 1024, 2) }} KB
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('permohonan.dokumen.download', $dokumen->id) }}"
                                                class="btn btn-sm btn-outline-primary" target="_blank" title="Download">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            @if (auth()->user()->hasAnyRole(['fasilitator', 'admin_peran', 'superadmin']))
                                                <form action="{{ route('permohonan.dokumen.delete', $dokumen->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Hapus">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class='bx bx-file bx-lg text-muted mb-3 d-block'></i>
                    <p class="text-muted mb-0">Belum ada dokumen pelaksanaan yang diupload.</p>
                    @if (auth()->user()->hasAnyRole(['fasilitator', 'admin_peran', 'superadmin']))
                        <small class="text-muted">Klik tombol "Upload Dokumen" untuk menambahkan.</small>
                    @endif
                </div>
            @endif

            <!-- Daftar Jenis Dokumen yang Dibutuhkan -->
            <div class="alert alert-light border mt-4">
                <strong><i class='bx bx-list-ul me-1'></i>Dokumen yang Diperlukan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Daftar Hadir Peserta</li>
                    <li>Berita Acara Pelaksanaan</li>
                    <li>Dokumentasi Foto Kegiatan</li>
                    <li>Notulensi Rapat (jika ada)</li>
                    <li>Dokumen Pendukung Lainnya</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Upload Dokumen Pelaksanaan -->
    @if (auth()->user()->hasAnyRole(['fasilitator', 'admin_peran', 'superadmin']))
        <div class="modal fade" id="uploadDokumenPelaksanaanModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class='bx bx-upload me-1'></i>Upload Dokumen Pelaksanaan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('permohonan.dokumen.upload-pelaksanaan', $permohonan->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="jenis_dokumen" class="form-label">Jenis Dokumen <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('jenis_dokumen') is-invalid @enderror"
                                    id="jenis_dokumen" name="jenis_dokumen" required>
                                    <option value="">Pilih Jenis Dokumen</option>
                                    <option value="Daftar Hadir">Daftar Hadir Peserta</option>
                                    <option value="Berita Acara">Berita Acara Pelaksanaan</option>
                                    <option value="Dokumentasi Foto">Dokumentasi Foto</option>
                                    <option value="Notulensi">Notulensi Rapat</option>
                                    <option value="Lainnya">Dokumen Lainnya</option>
                                </select>
                                @error('jenis_dokumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">File Dokumen <span
                                        class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror"
                                    id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <div class="form-text">
                                    Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 5MB.
                                </div>
                                @error('file')
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
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-upload me-1'></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif
