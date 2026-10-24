@extends('layouts.app')

@section('title', 'Detail Kabupaten/Kota')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Master Data
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kabupaten-kota.index') }}">Kabupaten/Kota</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('kabupaten-kota.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detail Data</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Kode</strong></td>
                                <td width="5%">:</td>
                                <td>{{ $kabupatenKota->kode }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama</strong></td>
                                <td>:</td>
                                <td>{{ $kabupatenKota->getFullNameAttribute() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis</strong></td>
                                <td>:</td>
                                <td>
                                    <span
                                        class="badge bg-label-{{ $kabupatenKota->jenis == 'kabupaten' ? 'primary' : 'info' }}">
                                        {{ ucfirst($kabupatenKota->jenis) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>:</td>
                                <td>
                                    @if ($kabupatenKota->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td>:</td>
                                <td>{{ $kabupatenKota->created_at ? $kabupatenKota->created_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Diupdate</strong></td>
                                <td>:</td>
                                <td>{{ $kabupatenKota->updated_at ? $kabupatenKota->updated_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                        </table>

                        <div class="mt-4">
                            <a href="{{ route('kabupaten-kota.edit', $kabupatenKota) }}" class="btn btn-primary me-2">
                                <i class="bx bx-edit-alt me-1"></i> Edit
                            </a>
                            <a href="{{ route('kabupaten-kota.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
