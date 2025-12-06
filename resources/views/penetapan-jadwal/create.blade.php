@extends('layouts.app')

@section('title', 'Penetapan Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Penetapan Jadwal Fasilitasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penetapan-jadwal.index') }}">Penetapan Jadwal</a></li>
                        <li class="breadcrumb-item active">Buat Penetapan</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
        </div>

        <div class="row">
            <!-- Informasi Permohonan -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Kabupaten/Kota</label>
                            <p class="fw-bold mb-0">{{ $permohonan->kabupatenKota->nama ?? '-' }}</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Jenis Dokumen</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span>
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Tahun</label>
                            <p class="fw-bold mb-0">{{ $permohonan->tahun }}</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Status Laporan</label>
                            <p class="mb-0">
                                @if ($permohonan->laporanVerifikasi)
                                    <span class="badge bg-success">
                                        <i class='bx bx-check'></i>
                                        {{ ucfirst($permohonan->laporanVerifikasi->status_kelengkapan) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Belum Ada Laporan</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Jadwal Tersedia -->
                @if ($jadwalTersedia->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class='bx bx-calendar-event'></i> Jadwal Tersedia
                            </h5>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">Ada {{ $jadwalTersedia->count() }} jadwal fasilitasi yang
                                tersedia</small>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Form Penetapan Jadwal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Form Penetapan Jadwal</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('penetapan-jadwal.store', $permohonan) }}" method="POST">
                            @csrf

                            <!-- Pilih Jadwal Fasilitasi (Optional) -->
                            <div class="mb-4">
                                <label for="jadwal_fasilitasi_id" class="form-label">
                                    Jadwal Fasilitasi yang Sudah Ada (Opsional)
                                </label>
                                <select name="jadwal_fasilitasi_id" id="jadwal_fasilitasi_id"
                                    class="form-select @error('jadwal_fasilitasi_id') is-invalid @enderror">
                                    <option value="">-- Buat Jadwal Baru atau Pilih dari yang Ada --</option>
                                    @foreach ($jadwalTersedia as $jadwal)
                                        <option value="{{ $jadwal->id }}"
                                            data-mulai="{{ $jadwal->tanggal_mulai->format('Y-m-d') }}"
                                            data-selesai="{{ $jadwal->tanggal_selesai->format('Y-m-d') }}"
                                            {{ old('jadwal_fasilitasi_id') == $jadwal->id ? 'selected' : '' }}>
                                            {{ $jadwal->tanggal_mulai->format('d M Y') }} -
                                            {{ $jadwal->tanggal_selesai->format('d M Y') }}
                                            @if ($jadwal->keterangan)
                                                ({{ $jadwal->keterangan }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Pilih jadwal yang sudah ada atau buat jadwal baru dengan mengisi tanggal di bawah
                                </small>
                                @error('jadwal_fasilitasi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class='bx bx-info-circle'></i>
                                <strong>Info:</strong> Jika memilih jadwal yang sudah ada, tanggal akan otomatis terisi.
                                Anda masih bisa mengubahnya jika diperlukan.
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="mb-4">
                                <label for="tanggal_mulai" class="form-label">
                                    Tanggal Mulai Fasilitasi <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                    class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                    value="{{ old('tanggal_mulai') }}" min="{{ date('Y-m-d') }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="mb-4">
                                <label for="tanggal_selesai" class="form-label">
                                    Tanggal Selesai Fasilitasi <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                    class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                    value="{{ old('tanggal_selesai') }}" min="{{ date('Y-m-d') }}" required>
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-4">
                                <label for="lokasi" class="form-label">
                                    Lokasi Pelaksanaan
                                </label>
                                <input type="text" name="lokasi" id="lokasi"
                                    class="form-control @error('lokasi') is-invalid @enderror" value="{{ old('lokasi') }}"
                                    placeholder="Contoh: Ruang Rapat Badan, Hotel XYZ, dll">
                                <small class="text-muted">
                                    Lokasi pelaksanaan fasilitasi (opsional)
                                </small>
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Catatan -->
                            <div class="mb-4">
                                <label for="catatan" class="form-label">
                                    Catatan Tambahan
                                </label>
                                <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="4">{{ old('catatan') }}</textarea>
                                <small class="text-muted">
                                    Catatan khusus terkait jadwal fasilitasi (opsional)
                                </small>
                                @error('catatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('penetapan-jadwal.index') }}" class="btn btn-secondary">
                                    <i class='bx bx-x'></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class='bx bx-calendar-check'></i> Tetapkan Jadwal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-fill tanggal ketika memilih jadwal yang sudah ada
            $('#jadwal_fasilitasi_id').on('change', function() {
                const selected = $(this).find(':selected');
                const tanggalMulai = selected.data('mulai');
                const tanggalSelesai = selected.data('selesai');

                if (tanggalMulai && tanggalSelesai) {
                    $('#tanggal_mulai').val(tanggalMulai);
                    $('#tanggal_selesai').val(tanggalSelesai);
                }
            });

            // Validasi tanggal selesai harus >= tanggal mulai
            $('#tanggal_mulai').on('change', function() {
                const tanggalMulai = $(this).val();
                $('#tanggal_selesai').attr('min', tanggalMulai);

                // Reset tanggal selesai jika lebih kecil dari mulai
                const tanggalSelesai = $('#tanggal_selesai').val();
                if (tanggalSelesai && tanggalSelesai < tanggalMulai) {
                    $('#tanggal_selesai').val(tanggalMulai);
                }
            });
        });
    </script>
@endpush
