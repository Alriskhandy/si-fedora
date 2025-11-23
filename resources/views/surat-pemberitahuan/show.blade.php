@extends('layouts.app')

@section('title', 'Detail Surat Pemberitahuan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detail Surat Pemberitahuan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Nomor Surat</strong></td>
                            <td width="5%">:</td>
                            <td>{{ $suratPemberitahuan->nomor_surat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Surat</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->tanggal_surat->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Perihal</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->perihal }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kabupaten/Kota</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jadwal Fasilitasi</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->jadwalFasilitasi->nama_kegiatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Isi Surat</strong></td>
                            <td>:</td>
                            <td>{!! nl2br(e($suratPemberitahuan->isi_surat)) !!}</td>
                        </tr>
                        <tr>
                            <td><strong>File Surat</strong></td>
                            <td>:</td>
                            <td>
                                @if($suratPemberitahuan->file_path)
                                    <a href="{{ route('surat-pemberitahuan.download', $suratPemberitahuan) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-download me-1"></i> Download File
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
                                @if($suratPemberitahuan->status == 'draft')
                                    <span class="badge bg-label-warning">Draft</span>
                                @elseif($suratPemberitahuan->status == 'sent')
                                    <span class="badge bg-label-success">Sent</span>
                                @elseif($suratPemberitahuan->status == 'received')
                                    <span class="badge bg-label-info">Received</span>
                                @else
                                    <span class="badge bg-label-secondary">Unknown</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dikirim</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->sent_at ? $suratPemberitahuan->sent_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->created_at ? $suratPemberitahuan->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Diupdate</strong></td>
                            <td>:</td>
                            <td>{{ $suratPemberitahuan->updated_at ? $suratPemberitahuan->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('surat-pemberitahuan.edit', $suratPemberitahuan) }}" class="btn btn-primary me-2">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a href="{{ route('surat-pemberitahuan.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection