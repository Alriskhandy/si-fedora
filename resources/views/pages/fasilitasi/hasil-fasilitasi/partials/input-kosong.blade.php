{{-- Keadaan kosong satu tab masukan hasil fasilitasi. Variabel: $bisaTambah (bool, opsional; petunjuk tombol Tambah Masukan). --}}
<div class="text-center text-muted border rounded py-4">
    <i class="bx bx-message-square-detail fs-2 d-block mb-1"></i>
    Belum ada masukan.
    @if ($bisaTambah ?? false)
        Klik <strong>Tambah Masukan</strong> untuk menambahkan.
    @endif
</div>
