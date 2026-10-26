{{--
    Field form urusan sesuai kamus data urusan. Variabel: $masterUrusan (null saat tambah).
    Kelompok, nama kelompok, rumpun, dan singkatan OPD pengampu diisi otomatis saat disimpan.
--}}
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label" for="kode_urusan">Kode Urusan <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('kode_urusan') is-invalid @enderror" id="kode_urusan" name="kode_urusan"
            value="{{ old('kode_urusan', $masterUrusan?->kode_urusan) }}" placeholder="X.XX" maxlength="5" required>
        <div class="form-text">Digit pertama = kode kelompok (1–8).</div>
        @error('kode_urusan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-9">
        <label class="form-label" for="nama_urusan">Nama Urusan <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama_urusan') is-invalid @enderror" id="nama_urusan" name="nama_urusan"
            value="{{ old('nama_urusan', $masterUrusan?->nama_urusan) }}" maxlength="150" required>
        <div class="form-text">Nomenklatur urusan.</div>
        @error('nama_urusan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <div class="bg-light rounded p-3 small">
            <div><span class="text-muted">Kelompok:</span> <span id="preview-kelompok">—</span></div>
            <div><span class="text-muted">Rumpun urusan:</span> <span id="preview-rumpun">—</span></div>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="kode_opd_pengampu">Kode OPD Pengampu</label>
        <input type="text" class="form-control @error('kode_opd_pengampu') is-invalid @enderror" id="kode_opd_pengampu" name="kode_opd_pengampu"
            value="{{ old('kode_opd_pengampu', $masterUrusan?->kode_opd_pengampu) }}" placeholder="82.XX.XX; 82.XX.XX.XX">
        <div class="form-text">Kode OPD dari master OPD; lebih dari satu dipisah titik koma (;). Singkatan OPD pengampu terisi otomatis.</div>
        @if ($masterUrusan?->opd_pengampu)
            <div class="form-text">OPD pengampu saat ini: {{ $masterUrusan->opd_pengampu }}</div>
        @endif
        @error('kode_opd_pengampu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach (App\Models\MasterUrusan::STATUS as $status)
                <option value="{{ $status }}" @selected(old('status', $masterUrusan?->status ?? App\Models\MasterUrusan::STATUS_AKTIF) === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <input type="hidden" name="tampil_hasil_fasilitasi" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="tampil_hasil_fasilitasi" name="tampil_hasil_fasilitasi" value="1"
                @checked(old('tampil_hasil_fasilitasi', $masterUrusan?->tampil_hasil_fasilitasi ?? true))>
            <label class="form-check-label" for="tampil_hasil_fasilitasi">Tampilkan di bagian urusan hasil fasilitasi</label>
        </div>
        <div class="form-text">Biasanya hanya urusan wajib pelayanan dasar, wajib non pelayanan dasar, dan pilihan (kelompok 1–3).</div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="tanggal_update">Tanggal Pemutakhiran <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('tanggal_update') is-invalid @enderror" id="tanggal_update" name="tanggal_update"
            value="{{ old('tanggal_update', now()->toDateString()) }}" required>
        @if ($masterUrusan?->tanggal_update)
            <div class="form-text">Sebelumnya: {{ $masterUrusan->tanggal_update->toDateString() }}</div>
        @endif
        @error('tanggal_update')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const kelompok = @json(App\Models\MasterUrusan::KELOMPOK);
            const input = document.getElementById('kode_urusan');

            function preview() {
                const item = kelompok[(input.value || '').charAt(0)];
                document.getElementById('preview-kelompok').textContent = item ? input.value.charAt(0) + ' — ' + item.nama : '—';
                document.getElementById('preview-rumpun').textContent = item ? item.rumpun : '—';
            }

            input.addEventListener('input', preview);
            preview();
        })();
    </script>
@endpush
