@extends('layouts.app')

@section('title', 'Detail Permohonan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Permohonan</h5>
                    <div>
                        <span class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Nomor Permohonan</strong></td>
                            <td width="5%">:</td>
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
                            <td><strong>Nama Dokumen</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->nama_dokumen }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tahun Anggaran</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->tahunAnggaran->tahun ?? '-' }} - {{ $permohonan->tahunAnggaran->nama ?? 'Tahun ' . $permohonan->tahunAnggaran->tahun }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jadwal Fasilitasi</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->jadwalFasilitasi->nama_kegiatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Permohonan</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->tanggal_permohonan ? \Carbon\Carbon::parse($permohonan->tanggal_permohonan)->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Keterangan</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->keterangan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>:</td>
                            <td>
                                <span class="badge bg-label-{{ $permohonan->status_badge_class }}">{{ $permohonan->status_label }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($permohonan->submitted_at)
                        <tr>
                            <td><strong>Dikirim</strong></td>
                            <td>:</td>
                            <td>{{ $permohonan->submitted_at ? \Carbon\Carbon::parse($permohonan->submitted_at)->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        @endif
                        @if($permohonan->verified_at)
                        <tr>
                            <td><strong>Diverifikasi</strong></td>
                            <td>:</td>
                            {{-- <td>{{ $permohonan->verified_at->format('d M Y H:i') }}</td> --}}
                            <td>{{ $permohonan->verified_at ? \Carbon\Carbon::parse($permohonan->verified_at)->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="mt-4">
                        @if($permohonan->status == 'draft' && auth()->user()->hasRole('kabupaten_kota'))
                        <a href="{{ route('permohonan.edit', $permohonan) }}" class="btn btn-primary me-2">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        @endif
                        <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dokumen Persyaratan Section -->
            {{-- <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dokumen Persyaratan</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Silakan upload dokumen persyaratan sesuai checklist di bawah ini.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Dokumen</th>
                                    <th>Upload</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permohonan->permohonanDokumen as $index => $dokumen)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dokumen->persyaratanDokumen->nama ?? 'Dokumen Persyaratan' }}</td>
                                    <td>
                                        @if($dokumen->file_path)
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-download me-1"></i> Lihat File
                                            </a>
                                        @else
                                            <span class="text-muted">Belum diupload</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dokumen->is_ada)
                                            <span class="badge bg-label-success">ADA</span>
                                        @else
                                            <span class="badge bg-label-danger">TIDAK ADA</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('permohonan-dokumen.edit', $dokumen) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-edit me-1"></i> Upload
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada dokumen persyaratan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($permohonan->status == 'draft' && auth()->user()->hasRole('kabupaten_kota'))
                    <div class="mt-3">
                        <a href="{{ route('permohonan-dokumen.create', ['permohonan_id' => $permohonan->id]) }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Dokumen
                        </a>
                    </div>
                    @endif
                </div>
            </div> --}}
            <!-- Dokumen Persyaratan -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Dokumen Persyaratan</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Silakan upload dokumen persyaratan sesuai checklist di bawah ini.</p>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Dokumen</th>
                        <th>Upload</th>
                        <th>Status</th>
                        <th>Catatan Verifikasi</th> <!-- Tambahin ini -->
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permohonan->permohonanDokumen as $index => $dokumen)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dokumen->persyaratanDokumen->nama ?? 'Dokumen Persyaratan' }}</td>
                        <td>
                            @if($dokumen->file_path)
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-download me-1"></i> Lihat File
                                            </a>
                                        @else
                                            <span class="text-muted">Belum diupload</span>
                                        @endif
                        </td>
                        <td>
                            @if($dokumen->is_ada)
                                <span class="badge bg-label-success">ADA</span>
                            @else
                                <span class="badge bg-label-danger">TIDAK ADA</span>
                            @endif
                        </td>
                        <td> <!-- Tambahin ini -->
                            @if($dokumen->catatan_verifikasi)
                                <small class="text-muted">{{ $dokumen->catatan_verifikasi }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('permohonan-dokumen.edit', $dokumen) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-edit me-1"></i> Upload
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada dokumen persyaratan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
@endsection