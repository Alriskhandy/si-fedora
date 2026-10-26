{{-- Pilihan OPD untuk akun ber-role opd; tampil/sembunyi lewat script di halaman. Variabel: $opdList, $selectedOpd. --}}
<div class="mb-3" id="opd-container" style="display: none;">
    <label class="form-label" for="opd_id">OPD</label>
    <select class="form-select @error('opd_id') is-invalid @enderror" id="opd_id" name="opd_id">
        <option value="">Pilih OPD</option>
        @foreach ($opdList as $opd)
            <option value="{{ $opd->id }}" @selected((string) old('opd_id', $selectedOpd) === (string) $opd->id)>
                {{ $opd->kode_opd }} — {{ $opd->nama_opd }}
            </option>
        @endforeach
    </select>
    @error('opd_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if ($opdList->isEmpty())
        <div class="form-text text-warning">Belum ada OPD aktif. Tambahkan lewat menu Master Data → OPD.</div>
    @endif
</div>
