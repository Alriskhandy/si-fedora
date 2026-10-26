{{--
    Bilah saring masukan pada satu tab input hasil fasilitasi (disaring di browser).
    Variabel: $bagian (sistematika|form|urusan|rekomendasi), $pilihanBagian (array id => label, opsional),
    $labelBagian (string, opsional), $denganOpd (bool, opsional), $denganSaya (bool, bawaan true), $denganUrutan (bool, opsional), $labelUrutan (string, opsional).
--}}
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap js-filter" data-bagian="{{ $bagian }}">
    <div class="btn-group btn-group-sm" role="group" aria-label="Saring pengirim">
        <button type="button" class="btn btn-outline-primary active" data-saring="semua">Semua</button>
        @if ($denganSaya ?? true)
            <button type="button" class="btn btn-outline-primary" data-saring="saya">Masukan Saya</button>
        @endif
        @if ($denganOpd ?? false)
            <button type="button" class="btn btn-outline-primary" data-saring="opd">Dari OPD</button>
        @endif
    </div>
    @if (! empty($pilihanBagian))
        <select class="form-select form-select-sm w-auto js-saring-bagian" aria-label="Saring {{ $labelBagian }}">
            <option value="">Semua {{ $labelBagian }}</option>
            @foreach ($pilihanBagian as $id => $label)
                <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
        </select>
    @endif
    @if ($denganUrutan ?? false)
        <select class="form-select form-select-sm w-auto ms-auto js-urutkan" aria-label="Urutkan masukan">
            <option value="urutan">Urut: {{ $labelUrutan ?? 'Urutan' }}</option>
            <option value="waktu">Urut: Waktu</option>
        </select>
    @endif
</div>
<div class="alert alert-secondary py-2 small d-none js-filter-kosong" data-bagian="{{ $bagian }}">
    <i class="bx bx-filter-alt"></i> Tidak ada masukan yang cocok dengan saringan.
</div>
