@extends('layouts.app')

@section('title', 'Detail Jenis Dokumen')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detail Jenis Dokumen</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Kode</strong></td>
                            <td width="5%">:</td>
                            <td>{{ $jenisDokumen->kode }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td>:</td>
                            <td>{{ $jenisDokumen->nama }}</td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi</strong></td>
                            <td>:</td>
                            <td>{{ $jenisDokumen->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>:</td>
                            <td>
                                @if($jenisDokumen->is_active)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat</strong></td>
                            <td>:</td>
                            <td>{{ $jenisDokumen->created_at ? $jenisDokumen->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Diupdate</strong></td>
                            <td>:</td>
                            <td>{{ $jenisDokumen->updated_at ? $jenisDokumen->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('jenis-dokumen.edit', $jenisDokumen) }}" class="btn btn-primary me-2">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a href="{{ route('jenis-dokumen.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection