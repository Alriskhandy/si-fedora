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
                            <td width="30%"><strong>Nama Kegiatan</strong></td>
                            <td width="5%">:</td>
                            <td>{{ $jadwal->nama_kegiatan }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tahun Anggaran</strong></td>
                            <td>:</td>
                            <td>{{ $jadwal->tahunAnggaran->tahun ?? '-' }} - {{ $jadwal->tahunAnggaran->nama ?? 'Tahun ' . $jadwal->tahunAnggaran->tahun }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Dokumen</strong></td>
                            <td>:</td>
                            <td>{{ $jadwal->jenisDokumen->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal</strong></td>
                            <td>:</td>
                            {{-- <td>{{ $jadwal->tanggal_mulai->format('d M Y') }} - {{ $jadwal->tanggal_selesai->format('d M Y') }}</td> --}}
                            <td>{{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d M Y') : '-' }} - {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Batas Permohonan</strong></td>
                            <td>:</td>
                            <td>{{ $jadwal->batas_permohonan ? $jadwal->batas_permohonan->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Keterangan</strong></td>
                            <td>:</td>
                            <td>{{ $jadwal->keterangan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>:</td>
                            <td>
                                @if($jadwal->status == 'draft')
                                    <span class="badge bg-label-warning">Draft</span>
                                @elseif($jadwal->status == 'published')
                                    <span class="badge bg-label-success">Published</span>
                                @else
                                    <span class="badge bg-label-danger">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat</strong></td>
                            <td>:</td>
                            <td>{{ $jadwal->created_at ? $jadwal->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Diupdate</strong></td>
                            <td>:</td>
                            <td>{{ $jadwal->updated_at ? $jadwal->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>

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