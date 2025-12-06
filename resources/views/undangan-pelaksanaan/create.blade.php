@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Buat Undangan Pelaksanaan</h4>
            <a href="{{ route('undangan-pelaksanaan.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <!-- Informasi Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kabupaten/Kota</dt>
                            <dd class="col-sm-7">{{ $permohonan->kabupatenKota->nama }}</dd>

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $permohonan->no_permohonan }}</dd>

                            <dt class="col-sm-5">Tanggal</dt>
                            <dd class="col-sm-7">{{ $permohonan->created_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Jadwal Fasilitasi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Jadwal Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        @if ($permohonan->penetapanJadwal)
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Tanggal Mulai</dt>
                                <dd class="col-sm-7">
                                    {{ $permohonan->penetapanJadwal->tanggal_mulai->format('d M Y') }}</dd>

                                <dt class="col-sm-5">Tanggal Selesai</dt>
                                <dd class="col-sm-7">
                                    {{ $permohonan->penetapanJadwal->tanggal_selesai->format('d M Y') }}</dd>

                                <dt class="col-sm-5">Lokasi</dt>
                                <dd class="col-sm-7">{{ $permohonan->penetapanJadwal->lokasi ?? '-' }}</dd>

                                <dt class="col-sm-5">Durasi</dt>
                                <dd class="col-sm-7">{{ $permohonan->penetapanJadwal->durasi_hari }} hari</dd>
                            </dl>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Form Undangan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('undangan-pelaksanaan.store', $permohonan) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="nomor_undangan">Nomor Undangan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nomor_undangan" name="nomor_undangan"
                                    value="{{ old('nomor_undangan', $nomorUndangan) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="perihal">Perihal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="perihal" name="perihal"
                                    value="{{ old('perihal', 'Undangan Pelaksanaan Fasilitasi Dokumen Perencanaan') }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="isi_undangan">Isi Undangan <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="isi_undangan" name="isi_undangan" rows="8" required>{{ old(
                                    'isi_undangan',
                                    "Dengan hormat,
                                
                                Bersama ini kami mengundang Saudara/i untuk menghadiri pelaksanaan fasilitasi dokumen perencanaan untuk {$permohonan->kabupatenKota->nama}.
                                
                                Waktu dan Tempat:
                                Tanggal: {$permohonan->penetapanJadwal->tanggal_mulai->format(
                                        'd F Y',
                                    )} - {$permohonan->penetapanJadwal->tanggal_selesai->format('d F Y')}
                                Lokasi: {$permohonan->penetapanJadwal->lokasi}
                                
                                Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.",
                                ) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="file_undangan">File Undangan (PDF)</label>
                                <input type="file" class="form-control" id="file_undangan" name="file_undangan"
                                    accept=".pdf">
                                <small class="text-muted">Format: PDF, Maksimal 2MB</small>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">Penerima Undangan <span class="text-danger">*</span></h6>

                            <div class="mb-3">
                                <label class="form-label">Verifikator</label>
                                <div class="border rounded p-3">
                                    @forelse ($verifikatorList as $verifikator)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="penerima[]"
                                                value="{{ $verifikator->id }}" id="ver_{{ $verifikator->id }}"
                                                {{ in_array($verifikator->id, old('penerima', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ver_{{ $verifikator->id }}">
                                                {{ $verifikator->name }} ({{ $verifikator->email }})
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Tidak ada verifikator tersedia</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fasilitator</label>
                                <div class="border rounded p-3">
                                    @forelse ($fasilitatorList as $fasilitator)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="penerima[]"
                                                value="{{ $fasilitator->id }}" id="fas_{{ $fasilitator->id }}"
                                                {{ in_array($fasilitator->id, old('penerima', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="fas_{{ $fasilitator->id }}">
                                                {{ $fasilitator->name }} ({{ $fasilitator->email }})
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Tidak ada fasilitator tersedia</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pemohon ({{ $permohonan->kabupatenKota->nama }})</label>
                                <div class="border rounded p-3">
                                    @forelse ($pemohonList as $pemohon)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="penerima[]"
                                                value="{{ $pemohon->id }}" id="pem_{{ $pemohon->id }}"
                                                {{ in_array($pemohon->id, old('penerima', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="pem_{{ $pemohon->id }}">
                                                {{ $pemohon->name }} ({{ $pemohon->email }})
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Tidak ada pemohon dari kabupaten/kota ini</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Simpan Undangan
                                </button>
                                <a href="{{ route('undangan-pelaksanaan.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
