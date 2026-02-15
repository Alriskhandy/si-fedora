@extends('layouts.app')

@section('title', 'Evaluasi Kabupaten/Kota')

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
                    <li class="breadcrumb-item active">Evaluasi</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('kabupaten-kota.index') }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back me-1'></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Evaluasi: {{ $kabupatenKota->nama }}</h5>
                            <p class="text-muted small mb-0">Kelola evaluasi kabupaten/kota</p>
                        </div>
                        @can('evaluasi.create')
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEvaluasiModal">
                            <i class="bx bx-plus me-1"></i> Tambah Data
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aspek</th>
                                    <th>Nilai</th>
                                    <th>Keterangan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evaluasi as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->aspek }}</td>
                                    <td>{{ $item->nilai }}</td>
                                    <td>{{ $item->keterangan }}</td>
                                    <td>
                                        @can('evaluasi.edit')
                                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editEvaluasiModal{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('evaluasi.delete')
                                        <form action="{{ route('evaluasi.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin menghapus data?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Evaluasi -->
@can('evaluasi.create')
<div class="modal fade" id="addEvaluasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('evaluasi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kabupaten_kota_id" value="{{ $kabupatenKota->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Evaluasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection