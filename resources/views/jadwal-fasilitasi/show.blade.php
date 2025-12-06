@extends('layouts.app')

@section('title', 'Detail Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detail Jadwal Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Tahun Anggaran</strong></td>
                                <td width="5%">:</td>
                                <td><span class="badge bg-label-primary">{{ $jadwal->tahun_anggaran }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Dokumen</strong></td>
                                <td>:</td>
                                <td><span class="badge bg-label-info">{{ strtoupper($jadwal->jenis_dokumen) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Periode Fasilitasi</strong></td>
                                <td>:</td>
                                <td>{{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d M Y') : '-' }} s/d
                                    {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d M Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Batas Permohonan</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($jadwal->batas_permohonan)
                                        <span
                                            class="badge bg-label-{{ $jadwal->batas_permohonan < now() ? 'danger' : 'success' }}">
                                            {{ $jadwal->batas_permohonan->format('d M Y') }}
                                            @if ($jadwal->batas_permohonan < now())
                                                (Sudah Lewat)
                                            @else
                                                ({{ $jadwal->batas_permohonan->diffForHumans() }})
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">Tidak ada batas</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>File Undangan</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($jadwal->undangan_file)
                                        <a href="{{ url('storage/' . $jadwal->undangan_file) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-file-blank"></i> Lihat File
                                        </a>
                                    @else
                                        <span class="text-muted">Tidak ada file</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($jadwal->status == 'draft')
                                        <span class="badge bg-label-secondary">Draft</span>
                                    @elseif($jadwal->status == 'published')
                                        <span class="badge bg-label-success">Published</span>
                                    @else
                                        <span class="badge bg-label-danger">Closed</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat Oleh</strong></td>
                                <td>:</td>
                                <td>{{ $jadwal->dibuatOleh?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat Tanggal</strong></td>
                                <td>:</td>
                                <td>{{ $jadwal->created_at ? $jadwal->created_at->format('d M Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Terakhir Diupdate</strong></td>
                                <td>:</td>
                                <td>{{ $jadwal->updated_at ? $jadwal->updated_at->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        </table>

                        <hr class="my-4">

                        <h6 class="mb-3">Permohonan Terkait Jadwal Ini</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwal->permohonan as $index => $p)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $p->kabupatenKota?->nama ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ $p->status_badge_class }}">
                                                    {{ $p->status_label }}
                                                </span>
                                            </td>
                                            <td>{{ $p->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada permohonan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('jadwal.edit', $jadwal) }}" class="btn btn-primary me-2">
                                <i class="bx bx-edit-alt me-1"></i> Edit
                            </a>
                            <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
