{{-- Field form OPD sesuai kamus data OPD. Variabel: $opd (null saat tambah), $urusanList. --}}
@php($selectedUrusan = array_map('intval', old('urusan', $opd?->urusan->pluck('id')->all() ?? [])))

<h6 class="text-muted text-uppercase small mb-3">Identitas</h6>
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <label class="form-label" for="kode_wilayah">Kode Wilayah <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('kode_wilayah') is-invalid @enderror" id="kode_wilayah" name="kode_wilayah"
            value="{{ old('kode_wilayah', $opd?->kode_wilayah ?? '82') }}" maxlength="2" required>
        @error('kode_wilayah')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="kode_opd">Kode OPD <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('kode_opd') is-invalid @enderror" id="kode_opd" name="kode_opd"
            value="{{ old('kode_opd', $opd?->kode_opd) }}" placeholder="82.XX.XX" maxlength="20" required>
        @error('kode_opd')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-7">
        <label class="form-label" for="nama_opd">Nama OPD <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama_opd') is-invalid @enderror" id="nama_opd" name="nama_opd"
            value="{{ old('nama_opd', $opd?->nama_opd) }}" maxlength="150" required>
        <div class="form-text">Nomenklatur resmi OPD.</div>
        @error('nama_opd')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="singkatan">Singkatan <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('singkatan') is-invalid @enderror" id="singkatan" name="singkatan"
            value="{{ old('singkatan', $opd?->singkatan) }}" maxlength="20" required>
        @error('singkatan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="jenis_opd">Jenis OPD <span class="text-danger">*</span></label>
        <select class="form-select @error('jenis_opd') is-invalid @enderror" id="jenis_opd" name="jenis_opd" required>
            <option value="">Pilih jenis</option>
            @foreach (App\Models\Opd::JENIS_OPD as $jenis)
                <option value="{{ $jenis }}" @selected(old('jenis_opd', $opd?->jenis_opd) === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
        @error('jenis_opd')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="rumpun_urusan">Rumpun Urusan <span class="text-danger">*</span></label>
        <select class="form-select @error('rumpun_urusan') is-invalid @enderror" id="rumpun_urusan" name="rumpun_urusan" required>
            <option value="">Pilih rumpun</option>
            @foreach (App\Models\MasterUrusan::rumpunList() as $rumpun)
                <option value="{{ $rumpun }}" @selected(old('rumpun_urusan', $opd?->rumpun_urusan) === $rumpun)>{{ $rumpun }}</option>
            @endforeach
        </select>
        @error('rumpun_urusan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="induk_opd">Kode OPD Induk</label>
        <input type="text" class="form-control @error('induk_opd') is-invalid @enderror" id="induk_opd" name="induk_opd"
            value="{{ old('induk_opd', $opd?->induk_opd) }}" placeholder="82.XX.XX" maxlength="20">
        <div class="form-text">Untuk UPTD/Biro.</div>
        @error('induk_opd')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="kode_urusan">Kode Urusan Diampu</label>
        <input type="text" class="form-control @error('kode_urusan') is-invalid @enderror" id="kode_urusan" name="kode_urusan"
            value="{{ old('kode_urusan', $opd?->kode_urusan) }}" placeholder="X.XX" maxlength="255">
        <div class="form-text">Lebih dari satu dipisah titik koma (;).</div>
        @error('kode_urusan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="urusan_pemerintahan">Urusan Pemerintahan Diampu</label>
        <input type="text" class="form-control @error('urusan_pemerintahan') is-invalid @enderror" id="urusan_pemerintahan" name="urusan_pemerintahan"
            value="{{ old('urusan_pemerintahan', $opd?->urusan_pemerintahan) }}" maxlength="255">
        <div class="form-text">Lebih dari satu dipisah titik koma (;).</div>
        @error('urusan_pemerintahan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach (App\Models\Opd::STATUS as $status)
                <option value="{{ $status }}" @selected(old('status', $opd?->status ?? App\Models\Opd::STATUS_AKTIF) === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="tanggal_update">Tanggal Pemutakhiran <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('tanggal_update') is-invalid @enderror" id="tanggal_update" name="tanggal_update"
            value="{{ old('tanggal_update', now()->toDateString()) }}" required>
        @if ($opd?->tanggal_update)
            <div class="form-text">Sebelumnya: {{ $opd->tanggal_update->toDateString() }}</div>
        @endif
        @error('tanggal_update')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h6 class="text-muted text-uppercase small mb-1">Urusan yang Dapat Diberi Masukan</h6>
<div class="form-text mb-2">Menentukan urusan pemerintahan yang dapat diberi masukan oleh akun OPD ini pada hasil fasilitasi. Hanya urusan yang ditampilkan di hasil fasilitasi (master urusan) yang tersedia.</div>
@error('urusan.*')
    <div class="text-danger small mb-2">{{ $message }}</div>
@enderror
<div class="row">
    @foreach ($urusanList as $urusan)
        <div class="col-md-6">
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="urusan[]" value="{{ $urusan->id }}"
                    id="urusan-{{ $urusan->id }}" @checked(in_array($urusan->id, $selectedUrusan, true))>
                <label class="form-check-label" for="urusan-{{ $urusan->id }}">{{ $urusan->kode_urusan }} {{ $urusan->nama_urusan }}</label>
            </div>
        </div>
    @endforeach
</div>
