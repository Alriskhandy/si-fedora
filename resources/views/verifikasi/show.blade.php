@extends('layouts.app')

@section('title', 'Verifikasi Permohonan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Verifikasi Permohonan</h5>
                    <a href="{{ route('verifikasi.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <!-- Info Permohonan -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Permohonan</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nomor Permohonan</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->nomor_permohonan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kabupaten/Kota</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Dokumen</strong></td>
                                    <td>:</td>
                                    <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Permohonan</strong></td>
                                    <td>:</td>
                                    {{-- <td>{{ $permohonan->tanggal_permohonan->format('d M Y') ?? '-' }}</td> --}}
                                    <td>{{ $permohonan->tanggal_permohonan ? \Carbon\Carbon::parse($permohonan->tanggal_permohonan)->format('d M Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Form Verifikasi -->
                    <form action="{{ route('verifikasi.verifikasi', $permohonan) }}" method="POST">
                        @csrf
                        
                        <h6>Dokumen Persyaratan</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permohonan->permohonanDokumen as $index => $dokumen)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $dokumen->persyaratanDokumen->nama ?? 'Dokumen Tidak Ditemukan' }}</td>
                                        <td>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" 
                                                       name="dokumen[{{ $dokumen->id }}][is_ada]" 
                                                       id="ada_{{ $dokumen->id }}" value="1"
                                                       {{ $dokumen->is_ada ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="ada_{{ $dokumen->id }}">ADA</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" 
                                                       name="dokumen[{{ $dokumen->id }}][is_ada]" 
                                                       id="tidak_ada_{{ $dokumen->id }}" value="0"
                                                       {{ !$dokumen->is_ada ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="tidak_ada_{{ $dokumen->id }}">TIDAK ADA</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" 
                                                   name="dokumen[{{ $dokumen->id }}][catatan]" 
                                                   placeholder="Catatan verifikasi"
                                                   value="{{ $dokumen->catatan_verifikasi ?? '' }}">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada dokumen persyaratan</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Catatan Umum & Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="catatan_umum">Catatan Umum</label>
                                <textarea class="form-control" id="catatan_umum" name="catatan_umum" rows="3" 
                                          placeholder="Catatan umum verifikasi...">{{ old('catatan_umum') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="status_verifikasi">Status Verifikasi</label>
                                <select class="form-select" id="status_verifikasi" name="status_verifikasi" required>
                                    <option value="verified">Dokumen LENGKAP</option>
                                    <option value="revision_required">Perlu REVISI</option>
                                </select>
                                <div class="form-text">
                                    Pilih "Perlu REVISI" jika ada dokumen yang tidak lengkap atau tidak sesuai.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success me-2">
                            <i class="bx bx-save me-1"></i> Simpan Verifikasi
                        </button>
                        <a href="{{ route('verifikasi.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i> Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection