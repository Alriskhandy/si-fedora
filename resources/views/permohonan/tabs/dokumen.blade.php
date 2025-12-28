<!-- Deadline Alert for Document Upload -->
@if ($permohonan->jadwalFasilitasi && $permohonan->jadwalFasilitasi->batas_permohonan)
    @if ($permohonan->isUploadDeadlinePassed())
        <div class="alert alert-danger">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Batas Waktu Terlewati!</strong><br>
            {{ $permohonan->getUploadDeadlineMessage() }}. Upload dokumen sudah tidak diperbolehkan.
        </div>
    @elseif (in_array($permohonan->status_akhir, ['belum', 'revisi']))
        <div class="alert alert-warning">
            <i class='bx bx-time-five me-2'></i>
            <strong>Perhatian!</strong><br>
            {{ $permohonan->getUploadDeadlineMessage() }}. Pastikan semua dokumen sudah diunggah sebelum batas waktu
            berakhir.
        </div>
    @endif
@endif

<!-- Dokumen Persyaratan Fasilitasi / Evaluasi -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class='bx bx-folder-open me-2'></i>Dokumen Persyaratan Fasilitasi / Evaluasi
            </h5>
        </div>

        @if ($permohonan->status_akhir == 'belum' && auth()->user()->hasRole('pemohon'))
            @php
                $totalDokumen = $permohonan->permohonanDokumen->count();
                $dokumenLengkap = $permohonan->permohonanDokumen->where('is_ada', true)->count();
                $progress = $totalDokumen > 0 ? ($dokumenLengkap / $totalDokumen) * 100 : 0;
            @endphp

            <!-- Progress Bar -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Kelengkapan Dokumen</small>
                    <small><strong
                            class="text-{{ $progress == 100 ? 'success' : 'warning' }}">{{ $dokumenLengkap }}/{{ $totalDokumen }}</strong></small>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar" role="progressbar"
                        style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            @if ($progress == 100)
                <form action="{{ route('permohonan.submit', $permohonan) }}" method="POST" id="submitPermohonanForm"
                    class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-success w-100" id="submitPermohonanBtn">
                        <i class='bx bx-send me-1'></i>Kirim Permohonan
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-outline-secondary w-100" disabled>
                    <i class='bx bx-lock me-1'></i>Lengkapi Dokumen untuk Submit
                </button>
            @endif
        @endif
    </div>
    <div class="card-body">
        @php
            // Gabungkan surat permohonan dan kelengkapan verifikasi
            $allDokumen = $permohonan->permohonanDokumen
                ->filter(function ($dok) {
                    return $dok->masterKelengkapan &&
                        in_array($dok->masterKelengkapan->kategori, ['surat_permohonan', 'kelengkapan_verifikasi']);
                })
                ->sortBy(function ($dok) {
                    // Prioritaskan surat permohonan di urutan pertama
                    return $dok->masterKelengkapan->kategori === 'surat_permohonan' ? 0 : 1;
                });
        @endphp

        @if ($allDokumen->count() > 0)
            @include('permohonan.partials.dokumen-table', ['dokumenList' => $allDokumen])
        @else
            <div class="text-center text-muted py-4">
                <i class='bx bx-folder-open bx-lg mb-2 d-block'></i>
                Belum ada dokumen persyaratan
            </div>
        @endif
    </div>
</div>
