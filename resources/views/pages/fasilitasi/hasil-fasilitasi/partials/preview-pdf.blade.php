{{-- Modal preview draft PDF hasil fasilitasi; iframe dimuat saat modal dibuka (daftar-script). Variabel: $permohonan, $hasilFasilitasi. --}}
<!-- Modal Preview PDF -->
<div class="modal fade" id="previewPdfModal" tabindex="-1" aria-labelledby="previewPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewPdfModalLabel"><i class="bx bx-file-blank"></i> Preview Dokumen Hasil Fasilitasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                @if ($hasilFasilitasi->draft_file)
                    <iframe id="pdfPreviewFrame" data-src="{{ route('hasil-fasilitasi.preview-pdf', $permohonan->id) }}"
                        width="100%" height="100%" style="border: none;" title="Preview PDF Hasil Fasilitasi"></iframe>
                @else
                    <div class="alert alert-info m-3"><i class="bx bx-info-circle"></i> Draft dokumen belum tersedia.</div>
                @endif
            </div>
            <div class="modal-footer">
                @if ($hasilFasilitasi->draft_file)
                    <a href="{{ route('hasil-fasilitasi.download-pdf', $permohonan->id) }}" class="btn btn-primary">
                        <i class="bx bx-download"></i> Download PDF
                    </a>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
