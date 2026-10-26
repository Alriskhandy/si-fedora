{{--
    Menu aksi (Ubah/Hapus) satu baris masukan. Data item dibaca dari elemen .js-item terdekat.
    Variabel: $bolehKelola (bool).
--}}
@if ($bolehKelola)
    <div class="dropdown">
        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary border-0 dropdown-toggle hide-arrow"
            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi masukan">
            <i class="bx bx-dots-vertical-rounded"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <button type="button" class="dropdown-item js-ubah"><i class="bx bx-edit-alt me-1"></i> Ubah</button>
            <button type="button" class="dropdown-item text-danger js-hapus"><i class="bx bx-trash me-1"></i> Hapus</button>
        </div>
    </div>
@endif
