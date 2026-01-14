@extends('layouts.app')

@section('title', 'Tambah Tim Assignment')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Master Data / Tim Assignment /</span> Tambah
        </h4>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Tambah Tim Assignment</h5>
                        <p class="text-muted small mb-0 mt-2">
                            <i class="bx bx-info-circle"></i> Pilih kabupaten/kota, tentukan PIC Verifikator, dan pilih
                            anggota Fasilitator
                        </p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tim-assignment.store') }}" method="POST">
                            @csrf

                            <!-- Kabupaten/Kota Selection -->
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3">
                                    <span class="badge bg-label-primary">1</span> Pilih Kabupaten/Kota
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label" for="kabupaten_kota_id">Kabupaten/Kota <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('kabupaten_kota_id') is-invalid @enderror"
                                        id="kabupaten_kota_id" name="kabupaten_kota_id" required>
                                        <option value="">Pilih Kabupaten/Kota</option>
                                        @foreach ($kabkotaList as $kabkota)
                                            <option value="{{ $kabkota->id }}"
                                                {{ old('kabupaten_kota_id') == $kabkota->id ? 'selected' : '' }}>
                                                {{ $kabkota->getFullNameAttribute() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kabupaten_kota_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- PIC Verifikator Selection -->
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3">
                                    <span class="badge bg-label-primary">2</span> Pilih PIC / Verifikator
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label" for="verifikator_id">PIC / Verifikator <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('verifikator_id') is-invalid @enderror"
                                        id="verifikator_id" name="verifikator_id" required>
                                        <option value="">Pilih Verifikator</option>
                                        @foreach ($verifikators as $verifikator)
                                            <option value="{{ $verifikator->id }}"
                                                {{ old('verifikator_id') == $verifikator->id ? 'selected' : '' }}>
                                                {{ $verifikator->name }} - {{ $verifikator->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('verifikator_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="bx bx-info-circle"></i> Verifikator akan otomatis dijadikan sebagai PIC
                                        untuk kabupaten/kota yang dipilih
                                    </small>
                                </div>
                            </div>

                            <!-- Fasilitator Selection (Checklist) -->
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3">
                                    <span class="badge bg-label-primary">3</span> Pilih Anggota Fasilitator
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label">Fasilitator <small class="text-muted">(opsional - pilih satu
                                            atau lebih)</small></label>
                                    @if ($fasilitators->isEmpty())
                                        <div class="alert alert-warning">
                                            <i class="bx bx-info-circle me-1"></i> Tidak ada user dengan role Fasilitator
                                        </div>
                                    @else
                                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                            @foreach ($fasilitators as $fasilitator)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="fasilitator_ids[]"
                                                        value="{{ $fasilitator->id }}"
                                                        id="fasilitator_{{ $fasilitator->id }}"
                                                        {{ is_array(old('fasilitator_ids')) && in_array($fasilitator->id, old('fasilitator_ids')) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="fasilitator_{{ $fasilitator->id }}">
                                                        {{ $fasilitator->name }}
                                                        <small class="text-muted">- {{ $fasilitator->email }}</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('fasilitator_ids')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        @error('fasilitator_ids.*')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            <!-- Period & Notes -->
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3">
                                    <span class="badge bg-label-secondary">4</span> Periode & Catatan <small
                                        class="text-muted">(opsional)</small>
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="assigned_from">Tanggal Mulai</label>
                                            <input type="date"
                                                class="form-control @error('assigned_from') is-invalid @enderror"
                                                id="assigned_from" name="assigned_from" value="{{ old('assigned_from') }}">
                                            @error('assigned_from')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Kosongkan jika berlaku dari sekarang</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="assigned_until">Tanggal Akhir</label>
                                            <input type="date"
                                                class="form-control @error('assigned_until') is-invalid @enderror"
                                                id="assigned_until" name="assigned_until"
                                                value="{{ old('assigned_until') }}">
                                            @error('assigned_until')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="notes">Catatan</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                        maxlength="1000">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maksimal 1000 karakter</small>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-save me-1"></i> Simpan
                                </button>
                                <a href="{{ route('tim-assignment.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bx bx-info-circle text-primary me-1"></i> Informasi Assignment
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-1"></i>
                                <strong>PIC Verifikator:</strong> Akan otomatis ditandai sebagai PIC untuk kabupaten/kota
                                yang dipilih
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-1"></i>
                                <strong>Anggota Fasilitator:</strong> Semua fasilitator yang dipilih akan ditambahkan
                                sebagai
                                anggota (bukan PIC)
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-1"></i>
                                <strong>Periode Assignment:</strong> Tanggal mulai dan akhir berlaku untuk semua anggota tim
                                yang ditambahkan
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-info-circle text-info me-1"></i>
                                Satu kabupaten/kota hanya bisa memiliki 1 PIC Verifikator
                            </li>
                            <li class="mb-0">
                                <i class="bx bx-info-circle text-info me-1"></i>
                                User tidak dapat di-assign lebih dari 1 kali untuk role yang sama di kabupaten/kota yang
                                sama
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
